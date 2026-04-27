<?php

namespace App\Livewire\Layout;

use Illuminate\Support\Facades\Route;
use Livewire\Component;

class SidebarMenuItem extends Component
{
    public array $item;

    public bool $collapsed;

    public bool $expanded = false;

    public function mount()
    {
        // Auto-expandir se algum filho estiver ativo
        if (isset($this->item['children'])) {
            foreach ($this->item['children'] as $child) {
                if ($child['active'] ?? false) {
                    $this->expanded = true;
                    break;
                }
            }
        }
    }

    public function toggle()
    {
        if (isset($this->item['children'])) {
            $this->expanded = ! $this->expanded;
        }
    }

    public function itemUrl(array $item): string
    {
        if (isset($item['route'])) {
            return Route::has($item['route']) ? route($item['route']) : '#';
        }

        return $item['url'] ?? '#';
    }

    public function render()
    {
        return view('livewire.layout.sidebar-menu-item');
    }
}
