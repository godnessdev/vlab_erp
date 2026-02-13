<?php

namespace App\Http\Controllers\Financeiro;

use App\Domain\Financeiro\Pagamento;
use App\Domain\Financeiro\FinanceiroService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PagamentoController
{
    public function index(Request $request): JsonResponse
    {
        $pagamentos = Pagamento::query()
            ->orderBy('data_pagamento', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json($pagamentos);
    }

    public function store(Request $request, FinanceiroService $service): JsonResponse
    {
        $validated = $request->validate([
            'conta_pagar_id' => 'required|uuid',
            'data_pagamento' => 'required|date',
            'valor_pago' => 'required|numeric',
            'forma_pagamento' => 'required|string',
        ]);
        $pagamento = $service->registrarPagamento($validated);
        return response()->json($pagamento, Response::HTTP_CREATED);
    }

    public function show(Pagamento $pagamento): JsonResponse
    {
        return response()->json($pagamento);
    }

    public function update(Request $request, Pagamento $pagamento): JsonResponse
    {
        $pagamento->update($request->all());
        return response()->json($pagamento);
    }

    public function destroy(Pagamento $pagamento): JsonResponse
    {
        $pagamento->delete();
        return response()->json(['message' => 'Pagamento excluído.']);
    }
}
