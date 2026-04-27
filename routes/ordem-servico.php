<?php

use App\Http\Controllers\OrdemServico\ItemOrdemServicoController;
use App\Http\Controllers\OrdemServico\OrdemServicoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Ordem de Serviço Routes
|--------------------------------------------------------------------------
|
| Rotas para gerenciamento de ordens de serviço e seus itens.
| Inclui operações CRUD completas e funcionalidades específicas.
|
*/

// Rotas principais de Ordem de Serviço
Route::prefix('ordens-servico')->name('ordens-servico.')->group(function () {

    // CRUD básico
    Route::get('/', [OrdemServicoController::class, 'index'])->name('index');
    Route::post('/', [OrdemServicoController::class, 'store'])->name('store');
    Route::get('/{ordem}', [OrdemServicoController::class, 'show'])->name('show');
    Route::put('/{ordem}', [OrdemServicoController::class, 'update'])->name('update');
    Route::delete('/{ordem}', [OrdemServicoController::class, 'destroy'])->name('destroy');

    // Rotas específicas
    Route::patch('/{ordem}/status', [OrdemServicoController::class, 'updateStatus'])->name('update-status');
    Route::patch('/{ordem}/equipe', [OrdemServicoController::class, 'assignTeam'])->name('assign-team');
    Route::get('/status/{status}', [OrdemServicoController::class, 'byStatus'])->name('by-status');
    Route::get('/reports/statistics', [OrdemServicoController::class, 'statistics'])->name('statistics');

    // Rotas para itens da ordem de serviço
    Route::prefix('{ordem}/itens')->name('itens.')->group(function () {
        Route::get('/', [ItemOrdemServicoController::class, 'index'])->name('index');
        Route::post('/', [ItemOrdemServicoController::class, 'store'])->name('store');
        Route::get('/{item}', [ItemOrdemServicoController::class, 'show'])->name('show');
        Route::put('/{item}', [ItemOrdemServicoController::class, 'update'])->name('update');
        Route::delete('/{item}', [ItemOrdemServicoController::class, 'destroy'])->name('destroy');
        Route::get('/total', [ItemOrdemServicoController::class, 'total'])->name('total');
    });
});
