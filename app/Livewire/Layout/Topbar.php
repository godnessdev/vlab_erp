<?php

namespace App\Livewire\Layout;

use Livewire\Attributes\Computed;
use Livewire\Component;

class Topbar extends Component
{
    #[Computed]
    public function currentCompany()
    {
        return auth()->user()->company ?? null;
    }

    #[Computed]
    public function breadcrumbs()
    {
        $route = request()->route();
        $routeName = $route ? $route->getName() : null;

        // Mapear rotas para breadcrumbs
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
            // Adicionar mais rotas conforme necessário
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
