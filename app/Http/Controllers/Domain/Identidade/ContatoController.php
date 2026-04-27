<?php

namespace App\Http\Controllers\Domain\Identidade;

use App\Domain\Identidade\Enums\TipoContato;
use App\Domain\Identidade\Models\Contato;
use App\Domain\Identidade\Models\Pessoa;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContatoController extends Controller
{
    /**
     * Mostra o formulário de criação de contato
     */
    public function create(Pessoa $pessoa): View
    {
        return view('domain.identidade.contatos.create', compact('pessoa'));
    }

    /**
     * Armazena um novo contato
     */
    public function store(Request $request, Pessoa $pessoa): RedirectResponse
    {
        $validated = $this->validateContato($request);

        $pessoa->contatos()->create($validated);

        return redirect()
            ->route('pessoas.show', $pessoa)
            ->with('success', 'Contato adicionado com sucesso!');
    }

    /**
     * Mostra o formulário de edição de contato
     */
    public function edit(Pessoa $pessoa, Contato $contato): View
    {
        abort_if($contato->pessoa_id !== $pessoa->id, 404);

        return view('domain.identidade.contatos.edit', compact('pessoa', 'contato'));
    }

    /**
     * Atualiza um contato
     */
    public function update(Request $request, Pessoa $pessoa, Contato $contato): RedirectResponse
    {
        abort_if($contato->pessoa_id !== $pessoa->id, 404);

        $validated = $this->validateContato($request);
        $contato->update($validated);

        return redirect()
            ->route('pessoas.show', $pessoa)
            ->with('success', 'Contato atualizado com sucesso!');
    }

    /**
     * Remove um contato
     */
    public function destroy(Pessoa $pessoa, Contato $contato): RedirectResponse
    {
        abort_if($contato->pessoa_id !== $pessoa->id, 404);

        $contato->delete();

        return redirect()
            ->route('pessoas.show', $pessoa)
            ->with('success', 'Contato removido com sucesso!');
    }

    /**
     * Valida dados do contato
     */
    private function validateContato(Request $request): array
    {
        $rules = [
            'tipo' => ['required', Rule::in(TipoContato::values())],
            'valor' => 'required|string|max:255',
            'principal' => 'sometimes|boolean',
            'observacoes' => 'nullable|string|max:500',
        ];

        // Validações específicas por tipo
        $tipo = $request->get('tipo');

        if ($tipo === 'EMAIL') {
            $rules['valor'] = 'required|email|max:255';
        } elseif (in_array($tipo, ['TELEFONE', 'CELULAR', 'WHATSAPP'])) {
            $rules['valor'] = 'required|string|regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/|max:255';
        } elseif ($tipo === 'SITE') {
            $rules['valor'] = 'required|url|max:255';
        }

        return $request->validate($rules);
    }
}
