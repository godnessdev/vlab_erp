<?php

namespace App\Http\Controllers\Domain\Identidade;

use App\Domain\Identidade\Enums\TipoDocumento;
use App\Domain\Identidade\Models\Documento;
use App\Domain\Identidade\Models\Pessoa;
use App\Domain\Identidade\Validators\DocumentoValidator;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DocumentoController extends Controller
{
    public function __construct(
        private readonly DocumentoValidator $documentoValidator
    ) {}

    /**
     * Mostra o formulário de criação de documento
     */
    public function create(Pessoa $pessoa): View
    {
        return view('domain.identidade.documentos.create', compact('pessoa'));
    }

    /**
     * Armazena um novo documento
     */
    public function store(Request $request, Pessoa $pessoa): RedirectResponse
    {
        $validated = $this->validateDocumento($request, $pessoa);

        // Validar documento com algoritmo específico
        $tipo = TipoDocumento::from($validated['tipo']);
        if (! $this->documentoValidator->validar($tipo, $validated['valor'])) {
            return back()
                ->withInput()
                ->withErrors(['valor' => "Documento {$tipo->label()} inválido."]);
        }

        $pessoa->documentos()->create([
            'tipo' => $validated['tipo'],
            'valor' => preg_replace('/\D/', '', $validated['valor']), // Limpar formatação
            'data_emissao' => $validated['data_emissao'] ?? null,
            'orgao_emissor' => $validated['orgao_emissor'] ?? null,
            'valido' => true,
        ]);

        return redirect()
            ->route('pessoas.show', $pessoa)
            ->with('success', 'Documento adicionado com sucesso!');
    }

    /**
     * Mostra o formulário de edição de documento
     */
    public function edit(Pessoa $pessoa, Documento $documento): View
    {
        abort_if($documento->pessoa_id !== $pessoa->id, 404);

        return view('domain.identidade.documentos.edit', compact('pessoa', 'documento'));
    }

    /**
     * Atualiza um documento
     */
    public function update(Request $request, Pessoa $pessoa, Documento $documento): RedirectResponse
    {
        abort_if($documento->pessoa_id !== $pessoa->id, 404);

        $validated = $this->validateDocumento($request, $pessoa, $documento);

        // Validar documento se o valor mudou
        if ($validated['valor'] !== $documento->valor) {
            $tipo = TipoDocumento::from($validated['tipo']);
            if (! $this->documentoValidator->validar($tipo, $validated['valor'])) {
                return back()
                    ->withInput()
                    ->withErrors(['valor' => "Documento {$tipo->label()} inválido."]);
            }
        }

        $documento->update([
            'tipo' => $validated['tipo'],
            'valor' => preg_replace('/\D/', '', $validated['valor']),
            'data_emissao' => $validated['data_emissao'] ?? null,
            'orgao_emissor' => $validated['orgao_emissor'] ?? null,
        ]);

        return redirect()
            ->route('pessoas.show', $pessoa)
            ->with('success', 'Documento atualizado com sucesso!');
    }

    /**
     * Remove um documento
     */
    public function destroy(Pessoa $pessoa, Documento $documento): RedirectResponse
    {
        abort_if($documento->pessoa_id !== $pessoa->id, 404);

        $documento->delete();

        return redirect()
            ->route('pessoas.show', $pessoa)
            ->with('success', 'Documento removido com sucesso!');
    }

    /**
     * Valida dados do documento
     */
    private function validateDocumento(Request $request, Pessoa $pessoa, ?Documento $documento = null): array
    {
        $rules = [
            'tipo' => ['required', Rule::in(TipoDocumento::values())],
            'valor' => [
                'required',
                'string',
                'max:50',
                Rule::unique('documentos', 'valor')
                    ->where('tipo', $request->get('tipo'))
                    ->ignore($documento?->id),
            ],
            'data_emissao' => 'nullable|date',
            'orgao_emissor' => 'nullable|string|max:100',
        ];

        $validated = $request->validate($rules);

        // Validar coerência tipo pessoa x tipo documento
        $tipo = TipoDocumento::from($validated['tipo']);

        if ($pessoa->tipo->isFisica() && $tipo === TipoDocumento::CNPJ) {
            throw ValidationException::withMessages([
                'tipo' => 'Pessoa física não pode ter CNPJ.',
            ]);
        }

        if ($pessoa->tipo->isJuridica() && $tipo === TipoDocumento::CPF) {
            throw ValidationException::withMessages([
                'tipo' => 'Pessoa jurídica não pode ter CPF.',
            ]);
        }

        return $validated;
    }
}
