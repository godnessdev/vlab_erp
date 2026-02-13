<?php

namespace App\Domain\Fiscal\Services;

use App\Domain\Fiscal\Repositories\RpsRepository;
use App\Domain\Fiscal\Repositories\FaturaRepository;
use App\Domain\Fiscal\Rps;
use App\Domain\Fiscal\Exceptions\RpsInvalidoException;
use App\Domain\Fiscal\Calculos\CalculadoraRetencoes;
use App\Domain\Fiscal\Helpers\SequencialGenerator;
use App\Domain\Fiscal\Helpers\DiscriminacaoMontador;
use App\Domain\Fiscal\Helpers\CodigoServicoResolver;
use Illuminate\Support\Facades\DB;

class RpsService
{
    protected $rpsRepository;
    protected $faturaRepository;
    protected $calculadoraRetencoes;
    protected $sequencialGenerator;
    protected $discriminacaoMontador;
    protected $codigoServicoResolver;
    protected $certificadoService;

    public function __construct(
        RpsRepository $rpsRepository,
        FaturaRepository $faturaRepository,
        CalculadoraRetencoes $calculadoraRetencoes,
        SequencialGenerator $sequencialGenerator,
        DiscriminacaoMontador $discriminacaoMontador,
        CodigoServicoResolver $codigoServicoResolver,
        $certificadoService = null // opcional para podeConverterParaNfse
    ) {
        $this->rpsRepository = $rpsRepository;
        $this->faturaRepository = $faturaRepository;
        $this->calculadoraRetencoes = $calculadoraRetencoes;
        $this->sequencialGenerator = $sequencialGenerator;
        $this->discriminacaoMontador = $discriminacaoMontador;
        $this->codigoServicoResolver = $codigoServicoResolver;
        $this->certificadoService = $certificadoService;
    }

    /**
     * Gera um novo RPS a partir de uma fatura, aplicando regras fiscais e validações.
     */
    public function gerarRps(string $faturaId): Rps
    {
        DB::beginTransaction();

        try {
            // 1. Validar fatura
            $fatura = $this->faturaRepository->findById($faturaId);
            if (!$fatura || $fatura->status === 'CANCELADA' || $fatura->valor_servicos <= 0) {
                throw new RpsInvalidoException('Fatura inválida para geração de RPS.');
            }

            // 2. Calcular impostos e retenções
            $dadosCalculo = [
                'valorServicos' => $fatura->valor_servicos,
                'valorDeducoes' => $fatura->valor_deducoes ?? 0,
                'regimeTributario' => $fatura->empresa->regime_tributario,
                'municipioPrestacao' => $fatura->municipio_prestacao,
                'tomador' => [
                    'tipo' => $fatura->cliente->tipo_pessoa,
                    'regime' => $fatura->cliente->regime_tributario ?? null,
                ],
                'codigoServico' => $this->codigoServicoResolver->obterCodigoServico($fatura),
                'dataEmissao' => now(),
            ];
            $impostos = $this->calculadoraRetencoes->calcularRetencoes($dadosCalculo);

            // 3. Obter próximo número RPS
            $numeroRps = $this->sequencialGenerator->obterProximoNumeroRps($fatura->empresa_id);

            // 4. Montar dados do RPS
            $dadosRps = [
                'empresa_id' => $fatura->empresa_id,
                'fatura_id' => $faturaId,
                'numero_rps' => $numeroRps,
                'serie' => $this->sequencialGenerator->obterSerieAtiva($fatura->empresa_id),
                'data_emissao' => now(),
                'competencia' => $fatura->mes_referencia,
                'valor_servicos' => $fatura->valor_servicos,
                'valor_deducoes' => $impostos['valorDeducoes'],
                'valor_pis' => $impostos['valorPIS'],
                'valor_cofins' => $impostos['valorCOFINS'],
                'valor_inss' => $impostos['valorINSS'],
                'valor_ir' => $impostos['valorIR'],
                'valor_csll' => $impostos['valorCSLL'],
                'base_calculo' => $impostos['baseCalculo'],
                'aliquota' => $impostos['aliquota'],
                'valor_iss' => $impostos['valorISS'],
                'valor_iss_retido' => $impostos['valorISSRetido'],
                'valor_ibs' => $impostos['valorIBS'] ?? 0,
                'valor_cbs' => $impostos['valorCBS'] ?? 0,
                'descricao' => $this->discriminacaoMontador->montarDiscriminacao($fatura),
                'codigo_servico' => $dadosCalculo['codigoServico'],
                'codigo_cnae' => $fatura->codigo_cnae ?? null,
                'item_lista_servico' => $this->codigoServicoResolver->obterItemListaServico($fatura),
                'situacao' => 'GERADO',
                'usar_layout_nacional' => now() >= new \DateTime('2026-01-01') || $fatura->empresa->usar_layout_nacional,
                'data_criacao' => now(),
            ];

            // 5. Persistir RPS
            $rps = $this->rpsRepository->create($dadosRps);

            // 6. Atualizar status da fatura
            $this->faturaRepository->atualizarStatus($faturaId, 'ENVIADA');

            DB::commit();
            return $rps;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Valida se um RPS pode ser convertido em NFS-e.
     */
    public function podeConverterParaNfse(Rps $rps): bool
    {
        if ($rps->situacao !== 'GERADO') {
            return false;
        }

        if ($rps->data_emissao->diffInDays(now()) > 5) {
            return false;
        }

        if (empty($rps->codigo_servico) || empty($rps->descricao) || $rps->valor_servicos <= 0) {
            return false;
        }

        // Verificar certificado ativo (lógica externa ou repository)
        return $this->certificadoService && $this->certificadoService->temCertificadoAtivo($rps->empresa_id);
    }
}
