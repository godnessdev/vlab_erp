<?php

use App\Domain\Identidade\Enums\TipoDocumento;
use App\Domain\Identidade\Services\PessoaService;
use App\Domain\Identidade\Validators\DocumentoValidator;
use App\Http\Controllers\Domain\Identidade\PessoaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Domínio Identidade
|--------------------------------------------------------------------------
|
| Aqui são registradas as rotas de API para o domínio de Identidade.
| Essas rotas são carregadas pelo RouteServiceProvider dentro do grupo
| de middleware "api" que inclui throttling e stateless authentication.
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Rotas de busca de pessoas
    Route::prefix('pessoas')->name('api.pessoas.')->group(function () {
        Route::get('buscar', [PessoaController::class, 'buscar'])->name('buscar');
        Route::get('buscar-documento/{documento}', [PessoaController::class, 'buscarPorDocumento'])->name('buscar-documento');

        // Rotas para autocomplete e seleção
        Route::get('autocomplete', function (Request $request) {
            $request->validate([
                'q' => 'required|string|min:2',
                'limit' => 'sometimes|integer|min:1|max:20',
            ]);

            $service = app(PessoaService::class);
            $pessoas = $service->buscarPorNome($request->get('q'));

            $limit = $request->get('limit', 10);
            $pessoas = $pessoas->take($limit);

            return response()->json([
                'data' => $pessoas->map(fn ($pessoa) => [
                    'id' => $pessoa->id,
                    'text' => $pessoa->getNomeCompleto(),
                    'subtitle' => $pessoa->getDocumentoPrincipal()?->getFormatado(),
                    'tipo' => $pessoa->tipo->label(),
                ]),
            ]);
        })->name('autocomplete');

        // Validação de documentos
        Route::post('validar-documento', function (Request $request) {
            $request->validate([
                'tipo' => 'required|in:CPF,CNPJ,RG,IE',
                'numero' => 'required|string',
            ]);

            $validator = app(DocumentoValidator::class);
            $tipo = TipoDocumento::from($request->get('tipo'));

            $valido = $validator->validar($tipo, $request->get('numero'));

            return response()->json([
                'valido' => $valido,
                'formatado' => $valido ? $validator->formatarDocumento($tipo, $request->get('numero')) : null,
                'mascara' => $validator->obterMascara($tipo),
            ]);
        })->name('validar-documento');
    });

    // Incluir rotas do domínio Empresa
    require __DIR__.'/empresa.php';

    // Rota para verificar autenticação
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
