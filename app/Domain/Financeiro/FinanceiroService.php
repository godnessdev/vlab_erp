<?php

namespace App\Domain\Financeiro;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class FinanceiroService
{
    /**
     * Cria uma conta a receber a partir de fatura
     */
    public function criarContaReceber(array $dados): ContaReceber
    {
        foreach ([
            'empresa_id',
            'fatura_id',
            'cliente_id',
            'numero_conta',
            'valor_original',
            'valor_liquido_esperado',
            'data_emissao',
            'data_vencimento',
            'forma_cobranca',
        ] as $campo) {
            if (empty($dados[$campo])) {
                throw new \InvalidArgumentException("Campo obrigatório ausente: {$campo}");
            }
        }

        return ContaReceber::create([
            'empresa_id' => $dados['empresa_id'],
            'fatura_id' => $dados['fatura_id'],
            'cliente_id' => $dados['cliente_id'],
            'numero_conta' => $dados['numero_conta'],
            'valor_original' => $dados['valor_original'],
            'valor_liquido_esperado' => $dados['valor_liquido_esperado'],
            'valor_total' => $dados['valor_liquido_esperado'],
            'data_emissao' => $dados['data_emissao'],
            'data_vencimento' => $dados['data_vencimento'],
            'forma_cobranca' => $dados['forma_cobranca'],
        ]);
    }

    /**
     * Cria uma conta a pagar
     */
    public function criarContaPagar(array $dados): ContaPagar
    {
        return ContaPagar::create($dados);
    }

    /**
     * Registra um recebimento
     */
    public function registrarRecebimento(array $dados): Recebimento
    {
        if (empty($dados['conta_receber_id'])) {
            throw new \InvalidArgumentException('conta_receber_id é obrigatório');
        }
        $contaReceber = ContaReceber::find($dados['conta_receber_id']);
        if (!$contaReceber) {
            throw new \InvalidArgumentException('ContaReceber não encontrada');
        }
        $recebimento = Recebimento::create([
            'conta_receber_id' => $contaReceber->id,
            'data_recebimento' => $dados['data_recebimento'] ?? now(),
            'valor_recebido' => $dados['valor_recebido'],
            'forma_recebimento' => $dados['forma_recebimento'],
        ]);
        $this->atualizarStatusContaReceber($recebimento->conta_receber_id);
        $this->atualizarFluxoCaixaRecebimento($recebimento);
        return $recebimento;
    }

    /**
     * Registra um pagamento
     */
    public function registrarPagamento(array $dados): Pagamento
    {
        if (empty($dados['conta_pagar_id'])) {
            throw new \InvalidArgumentException('conta_pagar_id é obrigatório');
        }
        $contaPagar = ContaPagar::find($dados['conta_pagar_id']);
        if (!$contaPagar) {
            throw new \InvalidArgumentException('ContaPagar não encontrada');
        }
        $pagamento = Pagamento::create([
            'conta_pagar_id' => $contaPagar->id,
            'data_pagamento' => $dados['data_pagamento'] ?? now(),
            'valor_pago' => $dados['valor_pago'],
            'forma_pagamento' => $dados['forma_pagamento'],
        ]);
        $this->atualizarStatusContaPagar($pagamento->conta_pagar_id);
        $this->atualizarFluxoCaixaPagamento($pagamento);
        return $pagamento;
    }

    /**
     * Concilia conta a receber com NFS-e
     */
    public function conciliarFiscalFinanceiro(array $dados): ConciliacaoFiscalFinanceiro
    {
        return ConciliacaoFiscalFinanceiro::create($dados);
    }

    /**
     * Calcula saldo acumulado do fluxo de caixa
     */
    public function calcularSaldoAcumulado(string $empresaId): float
    {
        if (empty($empresaId)) {
            throw new \InvalidArgumentException('empresa_id é obrigatório');
        }
        return FluxoCaixa::where('empresa_id', $empresaId)
            ->where('realizado', true)
            ->orderBy('data_referencia')
            ->sum('valor');
    }

    /**
     * Projeta fluxo de caixa futuro
     */
    public function projetarFluxoCaixa(string $empresaId, string $dataInicio, string $dataFim): array
    {
        return FluxoCaixa::where('empresa_id', $empresaId)
            ->whereBetween('data_referencia', [$dataInicio, $dataFim])
            ->get()
            ->groupBy('data_referencia')
            ->map(function ($dia) {
                return [
                    'entradas' => $dia->where('tipo_movimento', 'ENTRADA')->sum('valor'),
                    'saidas' => $dia->where('tipo_movimento', 'SAIDA')->sum('valor'),
                    'saldo' => $dia->where('tipo_movimento', 'ENTRADA')->sum('valor') - $dia->where('tipo_movimento', 'SAIDA')->sum('valor'),
                ];
            })
            ->toArray();
    }

    /**
     * Atualiza status automático de contas em atraso
     */
    public function atualizarStatusAutomatico(): void
    {
        ContaReceber::whereIn('status', ['ABERTA', 'PARCIAL'])
            ->where('data_vencimento', '<', Carbon::today())
            ->update(['status' => 'ATRASADA']);
        ContaPagar::whereIn('status', ['ABERTA', 'PARCIAL'])
            ->where('data_vencimento', '<', Carbon::today())
            ->update(['status' => 'ATRASADA']);
    }

    /**
     * Atualiza status da conta a receber após recebimento
     */
    private function atualizarStatusContaReceber(string $contaReceberId): void
    {
        $conta = ContaReceber::find($contaReceberId);
        $totalRecebido = Recebimento::where('conta_receber_id', $contaReceberId)->sum('valor_recebido');
        if ($totalRecebido >= $conta->valor_total) {
            $conta->status = 'PAGA';
        } elseif ($totalRecebido > 0) {
            $conta->status = 'PARCIAL';
        }
        $conta->save();
    }

    /**
     * Atualiza status da conta a pagar após pagamento
     */
    private function atualizarStatusContaPagar(string $contaPagarId): void
    {
        $conta = ContaPagar::find($contaPagarId);
        $totalPago = Pagamento::where('conta_pagar_id', $contaPagarId)->sum('valor_pago');
        if ($totalPago >= $conta->valor_total) {
            $conta->status = 'PAGA';
        } elseif ($totalPago > 0) {
            $conta->status = 'PARCIAL';
        }
        $conta->save();
    }

    /**
     * Atualiza fluxo de caixa após recebimento
     */
    private function atualizarFluxoCaixaRecebimento(Recebimento $recebimento): void
    {
        FluxoCaixa::create([
            'empresa_id' => ContaReceber::find($recebimento->conta_receber_id)->empresa_id,
            'data_referencia' => $recebimento->data_recebimento,
            'tipo_movimento' => 'ENTRADA',
            'categoria' => 'RECEBIMENTO',
            'valor' => $recebimento->valor_recebido,
            'descricao' => 'Recebimento de conta',
            'conta_receber_id' => $recebimento->conta_receber_id,
            'realizado' => true,
            'data_realizacao' => $recebimento->data_recebimento,
            'saldo_acumulado' => 0,
        ]);
    }

    /**
     * Atualiza fluxo de caixa após pagamento
     */
    private function atualizarFluxoCaixaPagamento(Pagamento $pagamento): void
    {
        FluxoCaixa::create([
            'empresa_id' => ContaPagar::find($pagamento->conta_pagar_id)->empresa_id,
            'data_referencia' => $pagamento->data_pagamento,
            'tipo_movimento' => 'SAIDA',
            'categoria' => 'PAGAMENTO',
            'valor' => $pagamento->valor_pago,
            'descricao' => 'Pagamento de conta',
            'conta_pagar_id' => $pagamento->conta_pagar_id,
            'realizado' => true,
            'data_realizacao' => $pagamento->data_pagamento,
            'saldo_acumulado' => 0,
        ]);
    }
}
