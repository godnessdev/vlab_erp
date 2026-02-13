<?php

namespace App\Domain\Faturamento\Services;

use App\Domain\Faturamento\Models\Fatura;
use App\Domain\Faturamento\Models\ItemFatura;
use App\Domain\Faturamento\Models\ParcelaFatura;
use App\Domain\Faturamento\Enums\StatusFatura;
use App\Domain\Faturamento\Enums\TipoParcelamento;
use App\Domain\OrdemServico\Models\OrdemServico;
use App\Domain\OrdemServico\Enums\StatusOrdemServico;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class FaturaService
{
    public function criarFaturaDeOrdens(
        string $empresaId,
        string $clienteId,
        Collection $ordensServico,
        array $dadosFatura = [],
        array $configParcelamento = []
    ): Fatura {
        // Validações iniciais
        $this->validarOrdensParaFaturamento($ordensServico);

        // Preparar dados da fatura
        $dadosFatura = array_merge([
            'empresa_id' => $empresaId,
            'cliente_id' => $clienteId,
            'data_emissao' => now()->toDateString(),
            'data_vencimento' => now()->addDays(30)->toDateString(),
            'mes_referencia' => now()->startOfMonth()->toDateString(),
            'status' => StatusFatura::ABERTA,
            'aliquota_iss' => 5.0, // Default 5%
        ], $dadosFatura);

        // Criar fatura
        $fatura = new Fatura($dadosFatura);
        
        // Calcular valores dos itens
        $this->calcularValoresFatura($fatura, $ordensServico);
        $fatura->save();

        // Criar itens da fatura
        $this->criarItensFatura($fatura, $ordensServico);

        // Criar parcelamento
        $this->criarParcelamento($fatura, $configParcelamento);

        // Atualizar status das ordens
        $this->atualizarStatusOrdens($ordensServico);

        return $fatura->fresh(['itens', 'parcelas']);
    }

    public function atualizarFatura(string $faturaId, array $dados): Fatura
    {
        $fatura = Fatura::findOrFail($faturaId);

        if (!$fatura->isEditavel()) {
            throw new InvalidArgumentException('Fatura não pode ser editada no status atual');
        }

        $fatura->fill($dados);
        $fatura->calcularTotais();
        $fatura->save();

        return $fatura->fresh();
    }

    public function alterarStatusFatura(string $faturaId, StatusFatura $novoStatus, string $observacao = null): Fatura
    {
        $fatura = Fatura::findOrFail($faturaId);

        if (!$fatura->status->podeTransicionarPara($novoStatus)) {
            throw new InvalidArgumentException(
                "Não é possível transicionar de {$fatura->status->value} para {$novoStatus->value}"
            );
        }

        $fatura->status = $novoStatus;
        
        if ($observacao) {
            $fatura->observacoes = ($fatura->observacoes ? $fatura->observacoes . "\n" : '') . 
                "[" . now()->format('d/m/Y H:i') . "] " . $observacao;
        }

        $fatura->save();

        return $fatura;
    }

    public function enviarFatura(string $faturaId, array $dadosEnvio = []): Fatura
    {
        $fatura = $this->alterarStatusFatura(
            $faturaId, 
            StatusFatura::ENVIADA,
            "Fatura enviada ao cliente"
        );

        // Aqui poderia integrar com serviço de e-mail ou API fiscal
        // $this->emailService->enviarFatura($fatura, $dadosEnvio);
        // $this->fiscalService->gerarRPS($fatura);

        return $fatura;
    }

    public function marcarComoPaga(string $faturaId, array $dadosPagamento): Fatura
    {
        $fatura = Fatura::findOrFail($faturaId);

        if ($fatura->status === StatusFatura::PAGA) {
            throw new InvalidArgumentException('Fatura já está marcada como paga');
        }

        // Marcar todas as parcelas como pagas
        foreach ($fatura->parcelas as $parcela) {
            if ($parcela->isPendente()) {
                $parcela->marcarComoPaga(
                    $parcela->valor_total_parcela,
                    $dadosPagamento['forma_pagamento'] ?? null,
                    isset($dadosPagamento['data_pagamento']) 
                        ? new \DateTime($dadosPagamento['data_pagamento'])
                        : null
                );
            }
        }

        return $this->alterarStatusFatura(
            $faturaId, 
            StatusFatura::PAGA,
            "Pagamento confirmado - " . ($dadosPagamento['observacao'] ?? '')
        );
    }

    public function cancelarFatura(string $faturaId, string $motivo): Fatura
    {
        $fatura = Fatura::findOrFail($faturaId);

        if (!$fatura->podeCancelar()) {
            throw new InvalidArgumentException('Fatura não pode ser cancelada no status atual');
        }

        // Cancelar todas as parcelas pendentes
        foreach ($fatura->parcelas as $parcela) {
            if ($parcela->isPendente()) {
                $parcela->cancelar();
            }
        }

        return $this->alterarStatusFatura(
            $faturaId, 
            StatusFatura::CANCELADA,
            "Fatura cancelada: " . $motivo
        );
    }

    public function buscarFaturasPorCliente(string $clienteId, array $filtros = []): Collection
    {
        $query = Fatura::porCliente($clienteId);

        if (isset($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        if (isset($filtros['data_inicio']) && isset($filtros['data_fim'])) {
            $query->whereBetween('data_emissao', [
                $filtros['data_inicio'],
                $filtros['data_fim']
            ]);
        }

        if (isset($filtros['vencidas_apenas']) && $filtros['vencidas_apenas']) {
            $query->vencidas();
        }

        return $query->with(['itens', 'parcelas'])
            ->orderByDesc('data_emissao')
            ->get();
    }

    public function buscarFaturasVencidas(string $empresaId): Collection
    {
        return Fatura::where('empresa_id', $empresaId)
            ->vencidas()
            ->with(['cliente', 'parcelas'])
            ->orderBy('data_vencimento')
            ->get();
    }

    public function calcularResumoFinanceiro(string $empresaId, int $mes, int $ano): array
    {
        $faturas = Fatura::where('empresa_id', $empresaId)
            ->porMesReferencia($ano, $mes)
            ->with('parcelas')
            ->get();

        $totalEmitido = $faturas->sum('valor_liquido');
        $totalPago = $faturas->sum(fn($f) => $f->getValorPago());
        $totalPendente = $totalEmitido - $totalPago;
        $faturasPagas = $faturas->where('status', StatusFatura::PAGA)->count();
        $faturasVencidas = $faturas->filter(fn($f) => $f->isVencida())->count();

        return [
            'total_emitido' => $totalEmitido,
            'total_pago' => $totalPago,
            'total_pendente' => $totalPendente,
            'quantidade_faturas' => $faturas->count(),
            'faturas_pagas' => $faturasPagas,
            'faturas_vencidas' => $faturasVencidas,
            'percentual_recebimento' => $totalEmitido > 0 ? ($totalPago / $totalEmitido) * 100 : 0,
        ];
    }

    // Métodos privados
    private function validarOrdensParaFaturamento(Collection $ordensServico): void
    {
        foreach ($ordensServico as $ordem) {
            if (!$ordem instanceof OrdemServico) {
                throw new InvalidArgumentException('Todas as ordens devem ser instâncias de OrdemServico');
            }

            if ($ordem->status !== StatusOrdemServico::CONCLUIDA) {
                throw new InvalidArgumentException("Ordem {$ordem->numero_ordem} não está concluída");
            }

            // Verificar se já foi faturada
            $jaFaturada = ItemFatura::where('ordem_servico_id', $ordem->id)->exists();
            if ($jaFaturada) {
                throw new InvalidArgumentException("Ordem {$ordem->numero_ordem} já foi faturada");
            }
        }
    }

    private function calcularValoresFatura(Fatura $fatura, Collection $ordensServico): void
    {
        $valorServicos = $ordensServico->sum('valor_total_final');
        
        $fatura->valor_servicos = $valorServicos;
        $fatura->calcularTotais();
    }

    private function criarItensFatura(Fatura $fatura, Collection $ordensServico): void
    {
        $sequencia = 1;

        foreach ($ordensServico as $ordem) {
            ItemFatura::create([
                'fatura_id' => $fatura->id,
                'ordem_servico_id' => $ordem->id,
                'sequencia' => $sequencia++,
                'descricao' => $ordem->titulo,
                'quantidade' => 1,
                'unidade_medida' => 'UN',
                'preco_unitario' => $ordem->valor_total_final ?? $ordem->valor_total_estimado,
                'subtotal' => $ordem->valor_total_final ?? $ordem->valor_total_estimado,
                'codigo_servico_municipal' => '0107', // Default - desenvolvimento software
                'aliquota_iss_item' => $fatura->aliquota_iss,
            ]);
        }
    }

    private function criarParcelamento(Fatura $fatura, array $config): void
    {
        $tipo = TipoParcelamento::from($config['tipo'] ?? TipoParcelamento::A_VISTA->value);
        $esquema = TipoParcelamento::getEsquemaParcelamento($tipo, $config);

        switch ($tipo) {
            case TipoParcelamento::A_VISTA:
                $this->criarParcelaUnica($fatura);
                break;
                
            case TipoParcelamento::FIXO:
                $this->criarParcelasFixas($fatura, $esquema);
                break;
                
            default:
                $this->criarParcelaUnica($fatura);
        }
    }

    private function criarParcelaUnica(Fatura $fatura): void
    {
        ParcelaFatura::create([
            'fatura_id' => $fatura->id,
            'numero_parcela' => 1,
            'data_vencimento' => $fatura->data_vencimento,
            'valor_parcela' => $fatura->valor_liquido,
            'valor_total_parcela' => $fatura->valor_liquido,
        ]);
    }

    private function criarParcelasFixas(Fatura $fatura, array $esquema): void
    {
        $quantidadeParcelas = $esquema['quantidade_parcelas'];
        $intervaloDias = $esquema['intervalo_dias'];
        $valorParcela = $fatura->valor_liquido / $quantidadeParcelas;

        for ($i = 1; $i <= $quantidadeParcelas; $i++) {
            $dataVencimento = $i === 1 
                ? $fatura->data_vencimento
                : now()->addDays($intervaloDias * ($i - 1))->toDateString();

            ParcelaFatura::create([
                'fatura_id' => $fatura->id,
                'numero_parcela' => $i,
                'data_vencimento' => $dataVencimento,
                'valor_parcela' => $valorParcela,
                'valor_total_parcela' => $valorParcela,
            ]);
        }
    }

    private function atualizarStatusOrdens(Collection $ordensServico): void
    {
        foreach ($ordensServico as $ordem) {
            // Marcar como faturada se necessário
            $ordem->update(['data_faturamento' => now()]);
        }
    }
}
