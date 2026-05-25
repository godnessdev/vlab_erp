<?php

namespace App\Livewire\Layout;

use Livewire\Attributes\Computed;
use Livewire\Component;

class Topbar extends Component
{
    #[Computed]
    public function currentCompany()
    {
        return tenant();
    }

    #[Computed]
    public function breadcrumbs(): array
    {
        $route = request()->route();
        $routeName = $route ? $route->getName() : null;

        $breadcrumbMap = [
            'dashboard' => [
                ['label' => 'Dashboard', 'route' => null],
            ],
            'empresas.ui' => [
                ['label' => 'Cadastros', 'route' => null],
                ['label' => 'Empresas', 'route' => null],
            ],
            'empresas.index' => [
                ['label' => 'Cadastros', 'route' => null],
                ['label' => 'Empresas', 'route' => null],
            ],
            'empresas.create' => [
                ['label' => 'Cadastros', 'route' => null],
                ['label' => 'Empresas', 'route' => 'empresas.ui'],
                ['label' => 'Nova Empresa', 'route' => null],
            ],
            'pessoas.index' => [
                ['label' => 'Cadastros', 'route' => null],
                ['label' => 'Pessoas', 'route' => null],
            ],
            'pessoas.create' => [
                ['label' => 'Cadastros', 'route' => null],
                ['label' => 'Pessoas', 'route' => 'pessoas.index'],
                ['label' => 'Nova Pessoa', 'route' => null],
            ],
            'servicos.index' => [
                ['label' => 'Cadastros', 'route' => null],
                ['label' => 'Servicos', 'route' => null],
            ],
            'ordens-servico.index' => [
                ['label' => 'Vendas', 'route' => null],
                ['label' => 'Ordens de Servico', 'route' => null],
            ],
            'profile.edit' => [
                ['label' => 'Configuracoes', 'route' => null],
                ['label' => 'Perfil', 'route' => null],
            ],
            'appearance.edit' => [
                ['label' => 'Configuracoes', 'route' => null],
                ['label' => 'Aparencia', 'route' => null],
            ],
        ];

        return $breadcrumbMap[$routeName] ?? [
            ['label' => 'Dashboard', 'route' => 'dashboard'],
        ];
    }

    public function render()
    {
        return view('livewire.layout.topbar');
    }
}
