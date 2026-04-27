<?php

namespace App\Http\Controllers\Domain\Identidade;

use App\Domain\Identidade\Models\Pessoa;
use App\Domain\Identidade\Services\PessoaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PessoaController extends Controller
{
    public function __construct(
        private readonly PessoaService $pessoaService
    ) {}

    /**
     * Lista todas as pessoas
     */
    public function index(Request $request): View
    {
        $query = Pessoa::with(['documentos', 'enderecos', 'contatos'])
            ->orderBy('nome_razao_social');

        // Filtros
        if ($request->filled('busca')) {
            $busca = $request->get('busca');
            $query->where(function ($q) use ($busca) {
                $q->where('nome_razao_social', 'like', "%{$busca}%")
                    ->orWhere('nome_fantasia', 'like', "%{$busca}%")
                    ->orWhereHas('documentos', function ($doc) use ($busca) {
                        $doc->where('valor', 'like', "%{$busca}%");
                    });
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->get('tipo'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $pessoas = $query->paginate(15);

        return view('domain.identidade.pessoas.index', compact('pessoas'));
    }

    /**
     * Mostra o formulário de criação
     */
    public function create(): View
    {
        return view('domain.identidade.pessoas.create');
    }

    /**
     * Armazena uma nova pessoa
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $this->validatePessoa($request);
            $pessoa = $this->pessoaService->criar($validated);

            return redirect()
                ->route('pessoas.show', $pessoa)
                ->with('success', 'Pessoa criada com sucesso!');

        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        }
    }

    /**
     * Mostra os detalhes de uma pessoa
     */
    public function show(Pessoa $pessoa): View
    {
        $pessoa->load([
            'documentos' => fn ($query) => $query->orderBy('tipo'),
            'enderecos' => fn ($query) => $query->orderBy('tipo'),
            'contatos' => fn ($query) => $query->orderBy('tipo'),
            'papeis.dadosEspecificos',
        ]);

        return view('domain.identidade.pessoas.show', compact('pessoa'));
    }

    /**
     * Mostra o formulário de edição
     */
    public function edit(Pessoa $pessoa): View
    {
        $pessoa->load(['documentos', 'enderecos', 'contatos']);

        return view('domain.identidade.pessoas.edit', compact('pessoa'));
    }

    /**
     * Atualiza uma pessoa
     */
    public function update(Request $request, Pessoa $pessoa): RedirectResponse
    {
        try {
            $validated = $this->validatePessoa($request, $pessoa);
            $this->pessoaService->atualizar($pessoa, $validated);

            return redirect()
                ->route('pessoas.show', $pessoa)
                ->with('success', 'Pessoa atualizada com sucesso!');

        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        }
    }

    /**
     * Remove uma pessoa
     */
    public function destroy(Pessoa $pessoa): RedirectResponse
    {
        try {
            $this->pessoaService->excluir($pessoa);

            return redirect()
                ->route('pessoas.index')
                ->with('success', 'Pessoa excluída com sucesso!');

        } catch (ValidationException $e) {
            return back()
                ->withErrors(['delete' => $e->getMessage()]);
        }
    }

    /**
     * Inativa uma pessoa
     */
    public function inativar(Pessoa $pessoa): RedirectResponse
    {
        $this->pessoaService->inativar($pessoa);

        return back()->with('success', 'Pessoa inativada com sucesso!');
    }

    /**
     * Ativa uma pessoa
     */
    public function ativar(Pessoa $pessoa): RedirectResponse
    {
        $this->pessoaService->ativar($pessoa);

        return back()->with('success', 'Pessoa ativada com sucesso!');
    }

    /**
     * Busca pessoas (API)
     */
    public function buscar(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'tipo' => 'sometimes|in:FISICA,JURIDICA',
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $pessoas = $this->pessoaService->buscarPorNome($request->get('q'));

        if ($request->filled('tipo')) {
            $pessoas = $pessoas->where('tipo', $request->get('tipo'));
        }

        $limit = $request->get('limit', 10);
        $pessoas = $pessoas->take($limit);

        return response()->json([
            'data' => $pessoas->map(function ($pessoa) {
                return [
                    'id' => $pessoa->id,
                    'nome' => $pessoa->getNomeCompleto(),
                    'tipo' => $pessoa->tipo->label(),
                    'documento_principal' => $pessoa->getDocumentoPrincipal()?->getFormatado(),
                ];
            }),
        ]);
    }

    /**
     * Busca pessoa por documento (API)
     */
    public function buscarPorDocumento(string $documento): JsonResponse
    {
        $pessoa = $this->pessoaService->buscarPorDocumento($documento);

        if (! $pessoa) {
            return response()->json(['message' => 'Pessoa não encontrada'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $pessoa->id,
                'nome' => $pessoa->getNomeCompleto(),
                'tipo' => $pessoa->tipo->label(),
                'documento_principal' => $pessoa->getDocumentoPrincipal()?->getFormatado(),
                'email_principal' => $pessoa->getEmailPrincipal(),
                'telefone_principal' => $pessoa->getTelefonePrincipal(),
            ],
        ]);
    }

    /**
     * Valida dados da pessoa
     */
    private function validatePessoa(Request $request, ?Pessoa $pessoa = null): array
    {
        $rules = [
            'tipo' => 'required|in:FISICA,JURIDICA',
            'nome_razao_social' => 'required|string|min:2|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'data_nascimento_constituicao' => 'nullable|date',
            'documentos' => 'sometimes|array',
            'documentos.*.tipo' => 'required_with:documentos|in:CPF,CNPJ,RG,IE',
            'documentos.*.valor' => 'required_with:documentos|string|max:50',
            'documentos.*.data_emissao' => 'nullable|date',
            'documentos.*.orgao_emissor' => 'nullable|string|max:100',
            'enderecos' => 'sometimes|array',
            'enderecos.*.tipo' => 'required_with:enderecos|in:RESIDENCIAL,COMERCIAL,CORRESPONDENCIA',
            'enderecos.*.logradouro' => 'required_with:enderecos|string|max:255',
            'enderecos.*.numero' => 'required_with:enderecos|string|max:20',
            'enderecos.*.complemento' => 'nullable|string|max:255',
            'enderecos.*.bairro' => 'required_with:enderecos|string|max:100',
            'enderecos.*.cidade' => 'required_with:enderecos|string|max:100',
            'enderecos.*.estado' => 'required_with:enderecos|string|size:2',
            'enderecos.*.cep' => 'required_with:enderecos|string|regex:/^\d{5}-?\d{3}$/',
            'contatos' => 'sometimes|array',
            'contatos.*.tipo' => 'required_with:contatos|in:EMAIL,TELEFONE,CELULAR,WHATSAPP,SITE',
            'contatos.*.valor' => 'required_with:contatos|string|max:255',
        ];

        // Validações específicas por tipo
        if ($request->get('tipo') === 'FISICA') {
            $rules['nome_fantasia'] = 'prohibited';
        }

        return $request->validate($rules);
    }
}
