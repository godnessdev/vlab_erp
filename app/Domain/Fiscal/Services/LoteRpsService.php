<?php

namespace App\Domain\Fiscal\Services;

use App\Domain\Fiscal\Exceptions\LoteInvalidoException;
use App\Domain\Fiscal\Helpers\XmlGenerator;
use App\Domain\Fiscal\Integrations\AcbrLibIntegration;
use App\Domain\Fiscal\LoteRps;
use App\Domain\Fiscal\Repositories\LoteRpsRepository;
use App\Domain\Fiscal\Repositories\RpsRepository;
use Illuminate\Support\Facades\DB;

class LoteRpsService
{
    protected $loteRpsRepository;

    protected $rpsRepository;

    protected $acbrLibIntegration;

    protected $xmlGenerator;

    protected $sequencialGenerator;

    protected $empresaRepository;

    public function __construct(
        LoteRpsRepository $loteRpsRepository,
        RpsRepository $rpsRepository,
        AcbrLibIntegration $acbrLibIntegration,
        XmlGenerator $xmlGenerator,
        $sequencialGenerator, // Tipo correto conforme implementação
        $empresaRepository // Tipo correto conforme implementação
    ) {
        $this->loteRpsRepository = $loteRpsRepository;
        $this->rpsRepository = $rpsRepository;
        $this->acbrLibIntegration = $acbrLibIntegration;
        $this->xmlGenerator = $xmlGenerator;
        $this->sequencialGenerator = $sequencialGenerator;
        $this->empresaRepository = $empresaRepository;
    }

    /**
     * Cria um novo lote de RPS para envio.
     * Regras de negócio:
     * - Validar RPS pendentes: mínimo 1, máximo por config (ex: 500).
     * - Calcular totais: serviços, deduções.
     * - Gerar número lote sequencial único por empresa.
     * - Persistir lote com status 'GERADO'.
     * - Vincular RPS ao lote e atualizar RPS para 'ENVIADO'.
     * - Suporte ambiente: nacional (ADN) ou municipal.
     */
    public function criarLote(string $empresaId): LoteRps
    {
        DB::beginTransaction();

        try {
            // 1. Buscar RPS pendentes
            $rpsPendentes = $this->rpsRepository->buscarPendentes($empresaId);
            if (empty($rpsPendentes) || count($rpsPendentes) > 500) {
                throw new LoteInvalidoException('Quantidade de RPS inválida para lote.');
            }

            // 2. Calcular totais
            $valorTotalServicos = collect($rpsPendentes)->sum('valor_servicos');
            $valorTotalDeducoes = collect($rpsPendentes)->sum('valor_deducoes');

            // 3. Obter próximo número lote
            $numeroLote = $this->sequencialGenerator->obterProximoNumeroLote($empresaId);

            // 4. Montar dados do lote
            $dadosLote = [
                'empresa_id' => $empresaId,
                'numero_lote' => $numeroLote,
                'data_geracao' => now(),
                'quantidade_rps' => count($rpsPendentes),
                'valor_total_servicos' => $valorTotalServicos,
                'valor_total_deducoes' => $valorTotalDeducoes,
                'inscricao_municipal' => $this->empresaRepository->getInscricaoMunicipal($empresaId),
                'cnpj' => $this->empresaRepository->getCnpj($empresaId),
                'status_envio' => 'GERADO',
                'ambiente' => $this->empresaRepository->getAmbienteFiscal($empresaId) ?? 'NACIONAL',
            ];

            // 5. Persistir lote
            $lote = $this->loteRpsRepository->create($dadosLote);

            // 6. Vincular RPS ao lote e atualizar status
            foreach ($rpsPendentes as $rps) {
                $this->loteRpsRepository->vincularRps($lote->id, $rps->id);
                $this->rpsRepository->atualizarSituacao($rps->id, 'ENVIADO');
            }

            DB::commit();

            return $lote;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Envia lote para prefeitura/ADN (mock ou integração real).
     * Regras de negócio:
     * - Lote deve estar 'GERADO'.
     * - Gerar XML/INI do lote (híbrido nacional/ABRASF).
     * - Enviar via ACBrLib (prioridade ADN, fallback municipal).
     * - Atualizar status para 'ENVIADO', persistir protocolo e data envio.
     * - Agendar job assíncrono para consulta de status/processamento.
     * - Tratar erros: retry automático (ex: timeout) ou erro permanente.
     */
    public function enviarLote(LoteRps $lote): bool
    {
        DB::beginTransaction();

        try {
            // 1. Validar lote
            if ($lote->status_envio !== 'GERADO') {
                throw new LoteInvalidoException('Lote inválido para envio.');
            }

            // 2. Buscar RPS vinculados
            $rpsVinculados = $this->rpsRepository->buscarPorLote($lote->id);

            // 3. Gerar XML/INI do lote
            $iniOrXml = $this->xmlGenerator->gerarXmlOuIniLote($lote, $rpsVinculados, $lote->ambiente === 'NACIONAL');

            // 4. Enviar via ACBrLib
            $retorno = $this->acbrLibIntegration->enviarLote($iniOrXml, $lote->empresa_id);

            if (! $retorno['sucesso']) {
                // Tratar retry se timeout
                if (strpos($retorno['mensagem'], 'TIMEOUT') !== false) {
                    // Agendar retry (ex: queue job)
                    return false;
                }
                throw new LoteInvalidoException('Erro no envio: '.$retorno['mensagem']);
            }

            // 5. Atualizar status
            $this->loteRpsRepository->atualizarEnvio($lote->id, [
                'status_envio' => 'ENVIADO',
                'protocolo_recebimento' => $retorno['protocolo'],
                'data_envio' => now(),
            ]);

            // 6. Agendar consulta de status (ex: Laravel Job)
            // ConsultaStatusLoteJob::dispatch($lote->id)->delay(30); // 30s

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
