<?php

namespace App\Http\Controllers\Financeiro;

use App\Domain\Financeiro\ConciliacaoFiscalFinanceiro;
use App\Domain\Financeiro\FinanceiroService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ConciliacaoFiscalFinanceiroController
{
    public function index(Request $request): JsonResponse
    {
        $conciliacoes = ConciliacaoFiscalFinanceiro::query()
            ->orderBy('data_conciliacao', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json($conciliacoes);
    }

    public function store(Request $request, FinanceiroService $service): JsonResponse
    {
        $validated = $request->validate([
            'conta_receber_id' => 'required|uuid',
            'nfse_id' => 'required|uuid',
            'data_conciliacao' => 'required|date',
            'valor_nfse' => 'required|numeric',
            'valor_conta_receber' => 'required|numeric',
            'valor_retencoes_nfse' => 'required|numeric',
            'valor_liquido_nfse' => 'required|numeric',
            'status_conciliacao' => 'required|string',
        ]);
        $conciliacao = $service->conciliarFiscalFinanceiro($validated);
        return response()->json($conciliacao, Response::HTTP_CREATED);
    }

    public function show(ConciliacaoFiscalFinanceiro $conciliacao): JsonResponse
    {
        return response()->json($conciliacao);
    }

    public function update(Request $request, ConciliacaoFiscalFinanceiro $conciliacao): JsonResponse
    {
        $conciliacao->update($request->all());
        return response()->json($conciliacao);
    }

    public function destroy(ConciliacaoFiscalFinanceiro $conciliacao): JsonResponse
    {
        $conciliacao->delete();
        return response()->json(['message' => 'Conciliação excluída.']);
    }
}
