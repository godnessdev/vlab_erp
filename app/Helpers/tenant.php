<?php

declare(strict_types=1);

use App\Models\Empresa;

if (! function_exists('tenant')) {
    /**
     * Obtém a empresa (tenant) atual
     */
    function tenant(): ?Empresa
    {
        if (! app()->bound('current.company')) {
            return null;
        }

        return app('current.company');
    }
}

if (! function_exists('tenant_id')) {
    /**
     * Obtém o ID da empresa (tenant) atual
     */
    function tenant_id(): ?string
    {
        $tenant = tenant();

        return $tenant?->id;
    }
}

if (! function_exists('has_tenant')) {
    /**
     * Verifica se há um tenant definido
     */
    function has_tenant(): bool
    {
        return tenant() !== null;
    }
}

if (! function_exists('switch_tenant')) {
    /**
     * Troca o tenant atual
     */
    function switch_tenant(string $empresaId): void
    {
        $user = auth()->user();

        if (! $user) {
            throw new RuntimeException('Usuário não autenticado');
        }

        // Verificar se usuário tem acesso a esta empresa
        $empresa = $user->empresas()->where('empresas.id', $empresaId)->first();

        if (! $empresa) {
            throw new RuntimeException('Usuário não tem acesso a esta empresa');
        }

        // Atualizar sessão
        session(['current_company_id' => $empresaId]);

        // Atualizar container
        app()->instance('current.company', $empresa);
    }
}
