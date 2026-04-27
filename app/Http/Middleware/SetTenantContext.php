<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de Tenant Context
 *
 * Define o contexto da empresa (tenant) atual para o usuário logado
 *
 * ✅ Multitenancy: Isola dados por empresa
 * ✅ Security: Verifica se usuário tem acesso à empresa
 * ✅ Performance: Cache do tenant context na sessão
 */
class SetTenantContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se não estiver autenticado, pular
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Buscar empresa do contexto (sessão ou primeira empresa do usuário)
        $empresaId = session('current_company_id');

        if ($empresaId) {
            // Verificar se usuário tem acesso a esta empresa
            $empresa = $user->empresas()
                ->where('empresas.id', $empresaId)
                ->first();
        } else {
            // Buscar primeira empresa do usuário
            $empresa = $user->empresas()->first();

            // Salvar na sessão
            if ($empresa) {
                session(['current_company_id' => $empresa->id]);
            }
        }

        // Definir empresa no container
        if ($empresa) {
            app()->instance('current.company', $empresa);

            // Também disponibilizar via helper
            app()->instance('tenant', $empresa);
        }

        return $next($request);
    }
}
