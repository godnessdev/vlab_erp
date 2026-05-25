<?php

namespace App\Livewire\Layout;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CompanySelector extends Component
{
    public bool $open = false;

    #[Computed]
    public function currentCompany()
    {
        return tenant();
    }

    #[Computed]
    public function availableCompanies()
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        return $user->empresas()
            ->wherePivot('status', 'ATIVO')
            ->orderBy('nome')
            ->get();
    }

    #[Computed]
    public function hasMultipleCompanies(): bool
    {
        return $this->availableCompanies->count() > 1;
    }

    public function selectCompany(string $companyId)
    {
        $company = $this->availableCompanies->firstWhere('id', $companyId);

        if (! $company) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Empresa nao encontrada ou sem permissao de acesso.',
            ]);

            return null;
        }

        switch_tenant($company->id);

        Cache::forget('dashboard.'.$company->id);

        Log::info('Usuario trocou de empresa no seletor da topbar.', [
            'usuario_id' => auth()->id(),
            'empresa_id' => $company->id,
        ]);

        $this->open = false;
        $this->dispatch('company-changed', companyId: $company->id);

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.layout.company-selector');
    }
}
