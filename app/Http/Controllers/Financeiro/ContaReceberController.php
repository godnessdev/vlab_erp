<?php

namespace App\Http\Controllers\Financeiro;

use App\Domain\Financeiro\ContaReceber;
use App\Domain\Financeiro\FinanceiroService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ContaReceberController
{
    public function index(Request $request): JsonResponse
    {
        $contas = ContaReceber::query()
            ->with(['cliente'])
            ->orderBy('data_vencimento', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json($contas);
    }

    public function store(Request $request, FinanceiroService $service): JsonResponse
    {
        $validated = $request->validate([
            'empresa_id' => 'required|uuid',
            'fatura_id' => 'required|uuid',
            'numero_conta' => 'required|string',
            'cliente_id' => 'required|uuid',
            'valor_original' => 'required|numeric',
            'valor_liquido_esperado' => 'required|numeric',
            'data_vencimento' => 'required|date',
            'data_emissao' => 'required|date',
            'forma_cobranca' => 'required|string',
        ]);
        $conta = $service->criarContaReceber($validated);
        return response()->json($conta, Response::HTTP_CREATED);
    }

    public function show(ContaReceber $conta): JsonResponse
    {
        return response()->json($conta);
    }

    public function update(Request $request, ContaReceber $conta): JsonResponse
    {
        $conta->update($request->all());
        return response()->json($conta);
    }

    public function destroy(ContaReceber $conta): JsonResponse
    {
        $conta->delete();
        return response()->json(['message' => 'Conta a receber excluída.']);
    }

    public function extrato(Request $request): JsonResponse
    {
        $empresaId = $request->get('empresa_id');
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');
        $contas = ContaReceber::where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->get();
        return response()->json($contas);
    }
}
