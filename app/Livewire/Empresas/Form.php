<?php

namespace App\Livewire\Empresas;

use App\Models\RegimeTributarioEnum;
use App\Services\EmpresaService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Form extends Component
{
    public $empresaId = null;

    public $modoEdicao = false;

    #[Validate('required|string|max:255')]
    public $nome = '';

    #[Validate('required|string|size:18')]
    public $cnpj = '';

    #[Validate('nullable|string|max:20')]
    public $ie = '';

    #[Validate('nullable|string|max:20')]
    public $im = '';

    #[Validate('required')]
    public $regime_tributario = '';

    #[Validate('nullable|date')]
    public $data_constituicao = '';

    #[Validate('nullable|email|max:100')]
    public $email_contato = '';

    #[Validate('nullable|string|max:20')]
    public $telefone_contato = '';

    public function mount($empresaId = null)
    {
        $this->empresaId = $empresaId;
        $this->modoEdicao = ! is_null($empresaId);

        if ($this->modoEdicao) {
            $this->carregarEmpresa();
        } else {
            // Valores padrão
            $this->regime_tributario = RegimeTributarioEnum::SIMPLES_NACIONAL->value;
        }
    }

    public function carregarEmpresa()
    {
        $empresaService = app(EmpresaService::class);
        $empresa = $empresaService->buscarEmpresaPorId($this->empresaId);

        if (! $empresa) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Empresa não encontrada.',
            ]);
            $this->dispatch('fechar-modal');

            return;
        }

        $this->nome = $empresa->nome;
        $this->cnpj = $empresa->formatarCnpj();
        $this->ie = $empresa->ie ?? '';
        $this->im = $empresa->im ?? '';
        $this->regime_tributario = $empresa->regime_tributario->value;
        $this->data_constituicao = $empresa->data_constituicao?->format('Y-m-d') ?? '';
        $this->email_contato = $empresa->email_contato ?? '';
        $this->telefone_contato = $empresa->telefone_contato ?? '';
    }

    public function formatarCnpj()
    {
        $cnpj = preg_replace('/\D/', '', $this->cnpj);

        if (strlen($cnpj) === 14) {
            $this->cnpj = preg_replace(
                '/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/',
                '$1.$2.$3/$4-$5',
                $cnpj
            );
        }
    }

    public function salvar()
    {
        $this->validate();

        try {
            $empresaService = app(EmpresaService::class);

            $dados = [
                'nome' => $this->nome,
                'cnpj' => $this->cnpj,
                'ie' => $this->ie ?: null,
                'im' => $this->im ?: null,
                'regime_tributario' => $this->regime_tributario,
                'data_constituicao' => $this->data_constituicao ?: null,
                'email_contato' => $this->email_contato ?: null,
                'telefone_contato' => $this->telefone_contato ?: null,
            ];

            if ($this->modoEdicao) {
                $empresaService->atualizarEmpresa($this->empresaId, $dados);
            } else {
                // Para criação, precisamos de um endereco_id temporário
                // TODO: Implementar seleção de endereço quando o módulo estiver pronto
                $dados['endereco_id'] = '00000000-0000-0000-0000-000000000000';
                $empresaService->criarEmpresa($dados);
            }

            $this->dispatch('empresa-salva');

        } catch (ValidationException $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro de validação: '.collect($e->errors())->flatten()->first(),
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro ao salvar empresa: '.$e->getMessage(),
            ]);
        }
    }

    public function cancelar()
    {
        $this->dispatch('fechar-modal');
    }

    public function render()
    {
        return view('livewire.empresas.form', [
            'regimes' => RegimeTributarioEnum::getOptions(),
        ]);
    }
}
