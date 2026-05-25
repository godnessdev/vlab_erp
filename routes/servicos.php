<?php

use App\Http\Controllers\Servicos\ServicoController;
use Illuminate\Support\Facades\Route;

// Rotas para serviços (requer autenticação)
Route::middleware(['auth', 'verified'])->prefix('servicos')->group(function () {

    // CRUD básico
    Route::get('/', [ServicoController::class, 'index'])->name('servicos.index');
    Route::post('/', [ServicoController::class, 'store'])->name('servicos.store');
    Route::get('/{id}', [ServicoController::class, 'show'])->name('servicos.show');
    Route::put('/{id}', [ServicoController::class, 'update'])->name('servicos.update');
    Route::delete('/{id}', [ServicoController::class, 'destroy'])->name('servicos.destroy');

    // Ações específicas
    Route::post('/{id}/ativar', [ServicoController::class, 'ativar'])->name('servicos.ativar');
    Route::post('/{id}/inativar', [ServicoController::class, 'inativar'])->name('servicos.inativar');

    // Funcionalidades avançadas
    Route::post('/{id}/calcular-tributacao', [ServicoController::class, 'calcularTributacao'])->name('servicos.calcular-tributacao');
    Route::post('/{id}/codigos-municipais', [ServicoController::class, 'adicionarCodigoMunicipal'])->name('servicos.codigos-municipais.store');
    Route::post('/{id}/regras-tributacao', [ServicoController::class, 'adicionarRegraTributacao'])->name('servicos.regras-tributacao.store');

    // Utilitários
    Route::get('/util/selecao', [ServicoController::class, 'selecao'])->name('servicos.selecao');
    Route::get('/util/estatisticas', [ServicoController::class, 'estatisticas'])->name('servicos.estatisticas');
});
