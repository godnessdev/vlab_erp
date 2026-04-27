<?php

namespace App\Http\Controllers\Domain\Identidade;

use App\Domain\Identidade\Enums\TipoEndereco;
use App\Domain\Identidade\Models\Endereco;
use App\Domain\Identidade\Models\Pessoa;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnderecoController extends Controller
{
    /**
     * Mostra o formulário de criação de endereço
     */
    public function create(Pessoa $pessoa): View
    {
        return view('domain.identidade.enderecos.create', compact('pessoa'));
    }

    /**
     * Armazena um novo endereço
     */
    public function store(Request $request, Pessoa $pessoa): RedirectResponse
    {
        $validated = $this->validateEndereco($request);

        $pessoa->enderecos()->create($validated);

        return redirect()
            ->route('pessoas.show', $pessoa)
            ->with('success', 'Endereço adicionado com sucesso!');
    }

    /**
     * Mostra o formulário de edição de endereço
     */
    public function edit(Pessoa $pessoa, Endereco $endereco): View
    {
        abort_if($endereco->pessoa_id !== $pessoa->id, 404);

        return view('domain.identidade.enderecos.edit', compact('pessoa', 'endereco'));
    }

    /**
     * Atualiza um endereço
     */
    public function update(Request $request, Pessoa $pessoa, Endereco $endereco): RedirectResponse
    {
        abort_if($endereco->pessoa_id !== $pessoa->id, 404);

        $validated = $this->validateEndereco($request);
        $endereco->update($validated);

        return redirect()
            ->route('pessoas.show', $pessoa)
            ->with('success', 'Endereço atualizado com sucesso!');
    }

    /**
     * Remove um endereço
     */
    public function destroy(Pessoa $pessoa, Endereco $endereco): RedirectResponse
    {
        abort_if($endereco->pessoa_id !== $pessoa->id, 404);

        $endereco->delete();

        return redirect()
            ->route('pessoas.show', $pessoa)
            ->with('success', 'Endereço removido com sucesso!');
    }

    /**
     * Valida dados do endereço
     */
    private function validateEndereco(Request $request): array
    {
        return $request->validate([
            'tipo' => ['required', Rule::in(TipoEndereco::values())],
            'logradouro' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:100',
            'cidade' => 'required|string|max:100',
            'estado' => 'required|string|size:2',
            'cep' => 'required|string|regex:/^\d{5}-?\d{3}$/',
            'observacoes' => 'nullable|string|max:500',
        ]);
    }
}
