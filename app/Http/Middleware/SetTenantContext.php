<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de Tenant Context.
 *
 * Define o contexto da empresa (tenant) atual para o usuario logado.
 */
class SetTenantContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $empresaId = session('current_company_id');
        $empresa = null;

        if ($empresaId) {
            $empresa = $user->empresas()
                ->where('empresas.id', $empresaId)
                ->first();
        }

        if (! $empresa) {
            $empresa = $user->empresas()->first();

            if ($empresa) {
                session(['current_company_id' => $empresa->id]);
            } else {
                session()->forget('current_company_id');
            }
        }

        app()->instance('current.company', $empresa);
        app()->instance('tenant', $empresa);

        return $next($request);
    }
}
