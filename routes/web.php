<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Domain\Identidade\PessoaController;
use App\Http\Controllers\Domain\Identidade\DocumentoController;
use App\Http\Controllers\Domain\Identidade\EnderecoController;
use App\Http\Controllers\Domain\Identidade\ContatoController;
use App\Http\Controllers\Domain\Identidade\PapelController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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
