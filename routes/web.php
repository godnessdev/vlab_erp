<?php

use App\Http\Controllers\Domain\Identidade\ContatoController;
use App\Http\Controllers\Domain\Identidade\DocumentoController;
use App\Http\Controllers\Domain\Identidade\EnderecoController;
use App\Http\Controllers\Domain\Identidade\PapelController;
use App\Http\Controllers\Domain\Identidade\PessoaController;
use App\Livewire\Dashboard\Index;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', Index::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Test route for new layout (temporary - remove after testing)
Route::get('test-layout', function () {
    return view('test-layout');
})->middleware(['auth', 'verified'])->name('test-layout');

// Rotas do domínio Identidade
Route::middleware(['auth', 'verified'])->group(function () {
    // Rotas de Pessoas
    Route::resource('pessoas', PessoaController::class);
    Route::prefix('pessoas/{pessoa}')->name('pessoas.')->group(function () {
        // Documentos
        Route::resource('documentos', DocumentoController::class)->except(['index', 'show']);

        // Endereços
        Route::resource('enderecos', EnderecoController::class)->except(['index', 'show']);

        // Contatos
        Route::resource('contatos', ContatoController::class)->except(['index', 'show']);

        // Papéis
        Route::resource('papeis', PapelController::class)->except(['index', 'show']);

        // Ações específicas de pessoa
        Route::patch('inativar', [PessoaController::class, 'inativar'])->name('inativar');
        Route::patch('ativar', [PessoaController::class, 'ativar'])->name('ativar');
    });

    // Rotas de busca
    Route::prefix('api/pessoas')->name('api.pessoas.')->group(function () {
        Route::get('buscar', [PessoaController::class, 'buscar'])->name('buscar');
        Route::get('buscar-documento/{documento}', [PessoaController::class, 'buscarPorDocumento'])->name('buscar-documento');
    });
});

// Incluir rotas dos módulos
require __DIR__.'/servicos.php';
require __DIR__.'/ordem-servico.php';
require __DIR__.'/fiscal.php';
require __DIR__.'/faturamento.php';
require __DIR__.'/empresa.php';
require __DIR__.'/financeiro.php';
require __DIR__.'/settings.php';

// Rotas de UI Livewire (devem vir depois das rotas de API para não conflitar)
Route::middleware(['auth', 'verified'])->group(function () {
    // Gestão de Empresas (UI)
    Route::get('gestao/empresas', App\Livewire\Empresas\Index::class)->name('empresas.ui');
});
