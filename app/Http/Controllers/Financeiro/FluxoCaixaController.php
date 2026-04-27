<?php

namespace App\Http\Controllers\Financeiro;

use App\Domain\Financeiro\FinanceiroService;
use App\Domain\Financeiro\FluxoCaixa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FluxoCaixaController
{
    public function index(Request $request): JsonResponse
    {
        $fluxos = FluxoCaixa::query()
            ->orderBy('data_referencia', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($fluxos);
    }

    public function saldo(Request $request, FinanceiroService $service): JsonResponse
    {
        $empresaId = $request->get('empresa_id');
        $saldo = $service->calcularSaldoAcumulado($empresaId);

        return response()->json(['empresa_id' => $empresaId, 'saldo_acumulado' => $saldo]);
    }

    public function projecao(Request $request, FinanceiroService $service): JsonResponse
    {
        $empresaId = $request->get('empresa_id');
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');
        $projecao = $service->projetarFluxoCaixa($empresaId, $dataInicio, $dataFim);

        return response()->json($projecao);
    }
}
