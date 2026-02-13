<?php

namespace App\Http\Controllers\Domain\Identidade;

use App\Http\Controllers\Controller;
use App\Domain\Identidade\Models\Pessoa;
use App\Domain\Identidade\Models\Papel;
use App\Domain\Identidade\Services\PessoaService;
use App\Domain\Identidade\Enums\TipoPapel;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PapelController extends Controller
{
    public function __construct(
        private readonly PessoaService $pessoaService
    ) {}

    /**
     * Mostra o formulário de criação de papel
     */
    public function create(Pessoa $pessoa): View
    {
        return view('domain.identidade.papeis.create', compact('pessoa'));
    }

    /**
     * Armazena um novo papel
     */
    public function store(Request $request, Pessoa $pessoa): RedirectResponse
    {
        try {
            $validated = $this->validatePapel($request);
            
            $this->pessoaService->adicionarPapel(
                $pessoa,
                $validated['tipo'],
                $validated['empresa_id'],
                $validated['dados_especificos'] ?? []
            );

            return redirect()
                ->route('pessoas.show', $pessoa)
                ->with('success', 'Papel adicionado com sucesso!');

        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        }
    }

    /**
     * Mostra o formulário de edição de papel
     */
    public function edit(Pessoa $pessoa, Papel $papel): View
    {
        abort_if($papel->pessoa_id !== $pessoa->id, 404);
        
        $papel->load('dadosEspecificos');
        
        return view('domain.identidade.papeis.edit', compact('pessoa', 'papel'));
    }

    /**
     * Atualiza um papel
     */
    public function update(Request $request, Pessoa $pessoa, Papel $papel): RedirectResponse
    {
        abort_if($papel->pessoa_id !== $pessoa->id, 404);
        
        $validated = $this->validatePapel($request, $papel);
        
        $papel->update([
            'data_fim' => $validated['data_fim'] ?? null,
            'observacoes' => $validated['observacoes'] ?? null,
        ]);

        // Atualizar dados específicos se fornecidos
        if (!empty($validated['dados_especificos'])) {
            // Remover dados antigos e adicionar novos
            $papel->dadosEspecificos()->delete();
            
            foreach ($validated['dados_especificos'] as $chave => $valor) {
                $papel->dadosEspecificos()->create([
                    'chave' => $chave,
                    'valor' => $valor,
                ]);
            }
        }

        return redirect()
            ->route('pessoas.show', $pessoa)
            ->with('success', 'Papel atualizado com sucesso!');
    }

    /**
     * Remove um papel
     */
    public function destroy(Pessoa $pessoa, Papel $papel): RedirectResponse
    {
        abort_if($papel->pessoa_id !== $pessoa->id, 404);
        
        try {
            $this->pessoaService->removerPapel($papel);

            return redirect()
                ->route('pessoas.show', $pessoa)
                ->with('success', 'Papel removido com sucesso!');

        } catch (ValidationException $e) {
            return back()
                ->withErrors(['delete' => $e->getMessage()]);
        }
    }

    /**
     * Valida dados do papel
     */
    private function validatePapel(Request $request, ?Papel $papel = null): array
    {
        $rules = [
            'tipo' => ['required', Rule::in(TipoPapel::values())],
            'empresa_id' => 'required|uuid',
            'data_inicio' => 'sometimes|date',
            'data_fim' => 'nullable|date|after:data_inicio',
            'observacoes' => 'nullable|string|max:1000',
            'dados_especificos' => 'sometimes|array',
            'dados_especificos.*' => 'nullable|string|max:500',
        ];

        // Para atualização, tipo e empresa não podem mudar
        if ($papel) {
            unset($rules['tipo'], $rules['empresa_id']);
        }

        return $request->validate($rules);
    }
}
