<?php

use App\Domain\Financeiro\ContaReceber;
use App\Domain\Financeiro\ContaPagar;
use App\Domain\Financeiro\Recebimento;
use App\Domain\Financeiro\Pagamento;
use App\Domain\Financeiro\ConciliacaoFiscalFinanceiro;
use App\Domain\Financeiro\FluxoCaixa;

it('cria conta a receber com factory', function () {
    $conta = ContaReceber::factory()->make();
    expect($conta)->toBeInstanceOf(ContaReceber::class);
});

it('cria conta a pagar com factory', function () {
    $conta = ContaPagar::factory()->make();
    expect($conta)->toBeInstanceOf(ContaPagar::class);
});

it('cria recebimento com factory', function () {
    $rec = Recebimento::factory()->make();
    expect($rec)->toBeInstanceOf(Recebimento::class);
});

it('cria pagamento com factory', function () {
    $pag = Pagamento::factory()->make();
    expect($pag)->toBeInstanceOf(Pagamento::class);
});

it('cria conciliação com factory', function () {
    $conc = ConciliacaoFiscalFinanceiro::factory()->make();
    expect($conc)->toBeInstanceOf(ConciliacaoFiscalFinanceiro::class);
});

it('cria fluxo de caixa com factory', function () {
    $fluxo = FluxoCaixa::factory()->make();
    expect($fluxo)->toBeInstanceOf(FluxoCaixa::class);
});
