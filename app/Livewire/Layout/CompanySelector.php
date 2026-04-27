<?php

namespace App\Livewire\Layout;

use App\Models\Empresa;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CompanySelector extends Component
{
    public bool $open = false;

    #[Computed]
    public function currentCompany()
    {
        return auth()->user()->company ?? null;
    }

    #[Computed]
    public function availableCompanies()
    {
        // Por enquanto, retornar apenas a empresa atual do usuário
        // TODO: Implementar quando sistema de múltiplas empresas estiver pronto
        $currentCompany = $this->currentCompany;

        return $currentCompany ? collect([$currentCompany]) : collect([]);
    }

    #[Computed]
    public function hasMultipleCompanies()
    {
        return $this->availableCompanies->count() > 1;
    }

    public function selectCompany($companyId)
    {
        $company = $this->availableCompanies->find($companyId);

        if (! $company) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Empresa não encontrada ou sem permissão de acesso.',
            ]);

            return;
        }

        // TODO: Implementar troca de empresa quando sistema multitenancy estiver completo
        // Por enquanto, apenas fechar o dropdown
        $this->open = false;

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Troca de empresa será implementada em breve.',
        ]);

        // Futuro código:
        // auth()->user()->update(['company_id' => $companyId]);
        // cache()->tags(['company:' . auth()->user()->id])->flush();
        // activity()->causedBy(auth()->user())->performedOn($company)->event('company_switched')->log('Usuário trocou para empresa: ' . $company->nome_fantasia);
        // $this->dispatch('company-changed', companyId: $companyId);
        // return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.layout.company-selector');
    }
}
