<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\FilialController;

/*
|--------------------------------------------------------------------------
| API Routes - Domínio Empresa
|--------------------------------------------------------------------------
*/

Route::prefix('empresas')->group(function () {
    // CRUD Empresas
    Route::get('/', [EmpresaController::class, 'index'])->name('empresas.index');
    Route::post('/', [EmpresaController::class, 'store'])->name('empresas.store');
    Route::get('/{empresa}', [EmpresaController::class, 'show'])->name('empresas.show');
    Route::put('/{empresa}', [EmpresaController::class, 'update'])->name('empresas.update');
    Route::delete('/{empresa}', [EmpresaController::class, 'destroy'])->name('empresas.destroy');
    
    // Ações específicas de empresa
    Route::post('/{empresa}/ativar', [EmpresaController::class, 'ativar'])->name('empresas.ativar');
    Route::post('/{empresa}/inativar', [EmpresaController::class, 'inativar'])->name('empresas.inativar');
    
    // Busca por CNPJ
    Route::post('/buscar-cnpj', [EmpresaController::class, 'buscarPorCnpj'])->name('empresas.buscar-cnpj');
    
    // Estatísticas e opções
    Route::get('/meta/estatisticas', [EmpresaController::class, 'estatisticas'])->name('empresas.estatisticas');
    Route::get('/meta/opcoes', [EmpresaController::class, 'opcoes'])->name('empresas.opcoes');
    
    // CRUD Filiais (aninhado em empresas)
    Route::prefix('/{empresa}/filiais')->group(function () {
        Route::get('/', [FilialController::class, 'index'])->name('filiais.index');
        Route::post('/', [FilialController::class, 'store'])->name('filiais.store');
        Route::get('/{filial}', [FilialController::class, 'show'])->name('filiais.show');
        Route::put('/{filial}', [FilialController::class, 'update'])->name('filiais.update');
        Route::delete('/{filial}', [FilialController::class, 'destroy'])->name('filiais.destroy');
        
        // Ações específicas de filial
        Route::post('/{filial}/ativar', [FilialController::class, 'ativar'])->name('filiais.ativar');
        Route::post('/{filial}/inativar', [FilialController::class, 'inativar'])->name('filiais.inativar');
        
        // Filial matriz
        Route::get('/matriz', [FilialController::class, 'matriz'])->name('filiais.matriz');
        
        // Estatísticas e opções
        Route::get('/meta/estatisticas', [FilialController::class, 'estatisticas'])->name('filiais.estatisticas');
        Route::get('/meta/opcoes', [FilialController::class, 'opcoes'])->name('filiais.opcoes');
    });
});
