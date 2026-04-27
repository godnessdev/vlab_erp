<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class StatCard extends Component
{
    public string $title;

    public float $value;

    public float $change;

    public string $trend; // up, down, neutral

    public string $icon;

    public string $format = 'number'; // number, currency, percentage

    public function getFormattedValue(): string
    {
        return match ($this->format) {
            'currency' => 'R$ '.number_format($this->value, 2, ',', '.'),
            'percentage' => number_format($this->value, 1, ',', '.').'%',
            default => number_format($this->value, 0, ',', '.'),
        };
    }

    public function render()
    {
        return view('livewire.dashboard.stat-card');
    }
}
