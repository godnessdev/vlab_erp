<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class QuickActionCard extends Component
{
    public string $title;

    public string $description;

    public string $icon;

    public string $route;

    public function render()
    {
        return view('livewire.dashboard.quick-action-card');
    }
}
