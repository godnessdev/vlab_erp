<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Financeiro\ContaReceberController;
use App\Http\Controllers\Financeiro\ContaPagarController;
use App\Http\Controllers\Financeiro\RecebimentoController;
use App\Http\Controllers\Financeiro\PagamentoController;
use App\Http\Controllers\Financeiro\ConciliacaoFiscalFinanceiroController;
use App\Http\Controllers\Financeiro\FluxoCaixaController;

Route::prefix('financeiro')->group(function () {
    // Conta a Receber
    Route::get('contas-receber', [ContaReceberController::class, 'index']);
    Route::post('contas-receber', [ContaReceberController::class, 'store']);
    Route::get('contas-receber/{conta}', [ContaReceberController::class, 'show']);
    Route::put('contas-receber/{conta}', [ContaReceberController::class, 'update']);
    Route::delete('contas-receber/{conta}', [ContaReceberController::class, 'destroy']);
    Route::get('contas-receber/extrato', [ContaReceberController::class, 'extrato']);

    // Conta a Pagar
    Route::get('contas-pagar', [ContaPagarController::class, 'index']);
    Route::post('contas-pagar', [ContaPagarController::class, 'store']);
    Route::get('contas-pagar/{conta}', [ContaPagarController::class, 'show']);
    Route::put('contas-pagar/{conta}', [ContaPagarController::class, 'update']);
    Route::delete('contas-pagar/{conta}', [ContaPagarController::class, 'destroy']);
    Route::get('contas-pagar/extrato', [ContaPagarController::class, 'extrato']);

    // Recebimento
    Route::get('recebimentos', [RecebimentoController::class, 'index']);
    Route::post('recebimentos', [RecebimentoController::class, 'store']);
    Route::get('recebimentos/{recebimento}', [RecebimentoController::class, 'show']);
    Route::put('recebimentos/{recebimento}', [RecebimentoController::class, 'update']);
    Route::delete('recebimentos/{recebimento}', [RecebimentoController::class, 'destroy']);

    // Pagamento
    Route::get('pagamentos', [PagamentoController::class, 'index']);
    Route::post('pagamentos', [PagamentoController::class, 'store']);
    Route::get('pagamentos/{pagamento}', [PagamentoController::class, 'show']);
    Route::put('pagamentos/{pagamento}', [PagamentoController::class, 'update']);
    Route::delete('pagamentos/{pagamento}', [PagamentoController::class, 'destroy']);

    // Conciliação Fiscal-Financeiro
    Route::get('conciliacoes', [ConciliacaoFiscalFinanceiroController::class, 'index']);
    Route::post('conciliacoes', [ConciliacaoFiscalFinanceiroController::class, 'store']);
    Route::get('conciliacoes/{conciliacao}', [ConciliacaoFiscalFinanceiroController::class, 'show']);
    Route::put('conciliacoes/{conciliacao}', [ConciliacaoFiscalFinanceiroController::class, 'update']);
    Route::delete('conciliacoes/{conciliacao}', [ConciliacaoFiscalFinanceiroController::class, 'destroy']);

    // Fluxo de Caixa
    Route::get('fluxo-caixa', [FluxoCaixaController::class, 'index']);
    Route::get('fluxo-caixa/saldo', [FluxoCaixaController::class, 'saldo']);
    Route::get('fluxo-caixa/projecao', [FluxoCaixaController::class, 'projecao']);
});
