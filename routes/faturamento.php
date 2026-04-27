<?php

use App\Http\Controllers\Faturamento\FaturaController;
use App\Http\Controllers\Faturamento\ParcelaFaturaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas do Módulo Faturamento
|--------------------------------------------------------------------------
|
| Rotas para gestão de faturas, parcelamento e controle de pagamentos
|
*/

Route::middleware(['auth:sanctum'])->prefix('api/faturamento')->group(function () {

    // === FATURAS ===
    Route::prefix('faturas')->group(function () {
        // CRUD básico
        Route::get('/', [FaturaController::class, 'index'])->name('faturas.index');
        Route::post('/', [FaturaController::class, 'store'])->name('faturas.store');
        Route::get('/{id}', [FaturaController::class, 'show'])->name('faturas.show');
        Route::put('/{id}', [FaturaController::class, 'update'])->name('faturas.update');

        // Ações específicas
        Route::post('/{id}/enviar', [FaturaController::class, 'enviar'])->name('faturas.enviar');
        Route::post('/{id}/marcar-paga', [FaturaController::class, 'marcarComoPaga'])->name('faturas.marcar-paga');
        Route::post('/{id}/cancelar', [FaturaController::class, 'cancelar'])->name('faturas.cancelar');

        // Relatórios
        Route::get('/relatorios/financeiro', [FaturaController::class, 'relatorioFinanceiro'])->name('faturas.relatorio-financeiro');
        Route::get('/relatorios/vencidas', [FaturaController::class, 'vencidas'])->name('faturas.vencidas');

        // Opções para selects
        Route::get('/meta/opcoes', [FaturaController::class, 'opcoes'])->name('faturas.opcoes');
    });

    // === PARCELAS ===
    Route::prefix('parcelas')->group(function () {
        // Listagem e detalhes
        Route::get('/', [ParcelaFaturaController::class, 'index'])->name('parcelas.index');
        Route::get('/{id}', [ParcelaFaturaController::class, 'show'])->name('parcelas.show');

        // Ações de pagamento
        Route::post('/{id}/marcar-paga', [ParcelaFaturaController::class, 'marcarComoPaga'])->name('parcelas.marcar-paga');
        Route::post('/{id}/cancelar', [ParcelaFaturaController::class, 'cancelar'])->name('parcelas.cancelar');

        // Cálculos
        Route::get('/{id}/calcular-juros-multa', [ParcelaFaturaController::class, 'calcularJurosMulta'])->name('parcelas.calcular-juros-multa');

        // Relatórios
        Route::get('/relatorios/periodo', [ParcelaFaturaController::class, 'relatorio'])->name('parcelas.relatorio');
        Route::get('/dashboard', [ParcelaFaturaController::class, 'dashboard'])->name('parcelas.dashboard');
    });

});

/*
|--------------------------------------------------------------------------
| Rotas Públicas (se necessário)
|--------------------------------------------------------------------------
*/

// Rota para visualização pública de fatura (com token)
Route::get('fatura-publica/{token}', function ($token) {
    // Implementar visualização pública da fatura
    return view('faturamento.publica', compact('token'));
})->name('fatura.publica');

/*
|--------------------------------------------------------------------------
| Rotas Administrativas
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'can:admin'])->prefix('api/admin/faturamento')->group(function () {

    // Relatórios gerenciais
    Route::get('relatorios/consolidado', function () {
        // Relatório consolidado de todas as empresas
    })->name('admin.faturamento.consolidado');

    // Configurações do sistema
    Route::get('configuracoes', function () {
        // Configurações globais do módulo
    })->name('admin.faturamento.configuracoes');

});
