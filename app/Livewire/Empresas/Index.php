<?php

namespace App\Livewire\Empresas;

use App\Models\RegimeTributarioEnum;
use App\Models\StatusEmpresaEnum;
use App\Services\EmpresaService;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $estatisticas = [];

    public int $perPage = 15;

    // Filtros
    #[Url]
    public $busca = '';

    #[Url]
    public $status = '';

    #[Url]
    public $regime_tributario = '';

    // Modal
    public $showModal = false;

    public $empresaSelecionada = null;

    public function mount(EmpresaService $empresaService)
    {
        $this->carregarEstatisticas($empresaService);
    }

    private function filtros(): array
    {
        return array_filter([
            'busca' => $this->busca,
            'status' => $this->status,
            'regime_tributario' => $this->regime_tributario,
        ]);
    }

    public function carregarEstatisticas(EmpresaService $empresaService)
    {
        $this->estatisticas = $empresaService->obterEstatisticas();
    }

    public function updatedBusca()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedRegimeTributario()
    {
        $this->resetPage();
    }

    public function limparFiltros()
    {
        $this->busca = '';
        $this->status = '';
        $this->regime_tributario = '';
        $this->resetPage();
    }

    public function abrirModal($empresaId = null)
    {
        $this->empresaSelecionada = $empresaId;
        $this->showModal = true;
    }

    public function fecharModal()
    {
        $this->showModal = false;
        $this->empresaSelecionada = null;
    }

    #[On('empresa-salva')]
    public function empresaSalva()
    {
        $this->fecharModal();
        $this->carregarEstatisticas(app(EmpresaService::class));
        $this->resetPage();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Empresa salva com sucesso!',
        ]);
    }

    public function ativar($empresaId)
    {
        try {
            $empresaService = app(EmpresaService::class);
            $empresaService->ativarEmpresa($empresaId);

            $this->carregarEstatisticas($empresaService);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Empresa ativada com sucesso!',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro ao ativar empresa: '.$e->getMessage(),
            ]);
        }
    }

    public function inativar($empresaId)
    {
        try {
            $empresaService = app(EmpresaService::class);
            $empresaService->inativarEmpresa($empresaId);

            $this->carregarEstatisticas($empresaService);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Empresa inativada com sucesso!',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro ao inativar empresa: '.$e->getMessage(),
            ]);
        }
    }

    public function excluir($empresaId)
    {
        try {
            $empresaService = app(EmpresaService::class);
            $empresaService->excluirEmpresa($empresaId);

            $this->carregarEstatisticas($empresaService);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Empresa excluída com sucesso!',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro ao excluir empresa: '.$e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        $empresaService = app(EmpresaService::class);
        $empresas = $empresaService
            ->listarEmpresasPaginadas($this->filtros(), $this->perPage)
            ->through(function ($empresa) {
                return [
                    'id' => $empresa->id,
                    'nome' => $empresa->nome,
                    'cnpj' => $empresa->formatarCnpj(),
                    'cnpj_raw' => $empresa->cnpj,
                    'regime_tributario' => [
                        'valor' => $empresa->regime_tributario->value,
                        'label' => $empresa->regime_tributario->getLabel(),
                    ],
                    'status' => [
                        'valor' => $empresa->status->value,
                        'label' => $empresa->status->getLabel(),
                        'cor' => $empresa->status->getCor(),
                    ],
                    'data_constituicao' => $empresa->data_constituicao?->format('d/m/Y'),
                    'total_filiais' => $empresa->filiais->count(),
                    'created_at' => $empresa->created_at->format('d/m/Y H:i'),
                ];
            });

        return view('livewire.empresas.index', [
            'empresas' => $empresas,
            'regimes' => RegimeTributarioEnum::getOptions(),
            'statusOptions' => StatusEmpresaEnum::getOptions(),
        ]);
    }
}
