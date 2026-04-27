<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class Sidebar extends Component
{
    public bool $collapsed = false;

    public array $expandedMenus = [];

    public function mount()
    {
        // Estado será restaurado via Alpine.js do localStorage
        $this->collapsed = false;
    }

    public function toggleCollapse()
    {
        $this->collapsed = ! $this->collapsed;
        $this->dispatch('sidebar-toggled', collapsed: $this->collapsed);
    }

    public function toggleMenu(string $menuId)
    {
        if (in_array($menuId, $this->expandedMenus)) {
            $this->expandedMenus = array_diff($this->expandedMenus, [$menuId]);
        } else {
            $this->expandedMenus[] = $menuId;
        }
    }

    public function getMenuItems(): array
    {
        return [
            [
                'id' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => 'home',
                'route' => 'dashboard',
                'active' => request()->routeIs('dashboard'),
            ],
            [
                'id' => 'cadastros',
                'label' => 'Cadastros',
                'icon' => 'folder',
                'children' => [
                    [
                        'label' => 'Empresas',
                        'route' => 'empresas.ui',
                        'active' => request()->routeIs('empresas.*'),
                    ],
                    [
                        'label' => 'Pessoas',
                        'route' => 'pessoas.index',
                        'active' => request()->routeIs('pessoas.*'),
                    ],
                    [
                        'label' => 'Serviços',
                        'route' => 'servicos.index',
                        'active' => request()->routeIs('servicos.*'),
                    ],
                ],
            ],
            [
                'id' => 'vendas',
                'label' => 'Vendas',
                'icon' => 'shopping-cart',
                'children' => [
                    [
                        'label' => 'Ordens de Serviço',
                        'route' => 'ordens-servico.index',
                        'active' => request()->routeIs('ordens-servico.*'),
                    ],
                ],
            ],
            [
                'id' => 'financeiro',
                'label' => 'Financeiro',
                'icon' => 'currency-dollar',
                'children' => [
                    [
                        'label' => 'Contas a Receber',
                        'url' => '/financeiro/contas-receber',
                        'active' => request()->is('financeiro/contas-receber*'),
                    ],
                    [
                        'label' => 'Contas a Pagar',
                        'url' => '/financeiro/contas-pagar',
                        'active' => request()->is('financeiro/contas-pagar*'),
                    ],
                    [
                        'label' => 'Fluxo de Caixa',
                        'url' => '/financeiro/fluxo-caixa',
                        'active' => request()->is('financeiro/fluxo-caixa*'),
                    ],
                ],
            ],
            [
                'id' => 'fiscal',
                'label' => 'Fiscal',
                'icon' => 'document-text',
                'children' => [
                    [
                        'label' => 'NFS-e',
                        'url' => '/fiscal/nfse',
                        'active' => request()->is('fiscal/nfse*'),
                    ],
                    [
                        'label' => 'RPS',
                        'url' => '/fiscal/rps',
                        'active' => request()->is('fiscal/rps*'),
                    ],
                    [
                        'label' => 'Lotes',
                        'url' => '/fiscal/lotes',
                        'active' => request()->is('fiscal/lotes*'),
                    ],
                ],
            ],
            [
                'id' => 'relatorios',
                'label' => 'Relatórios',
                'icon' => 'chart-bar',
                'route' => 'relatorios.index',
                'active' => request()->routeIs('relatorios.*'),
            ],
        ];
    }

    public function render()
    {
        return view('livewire.layout.sidebar', [
            'menuItems' => $this->getMenuItems(),
        ]);
    }
}
