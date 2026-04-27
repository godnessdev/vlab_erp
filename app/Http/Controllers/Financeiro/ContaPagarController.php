<?php

namespace App\Http\Controllers\Financeiro;

use App\Domain\Financeiro\ContaPagar;
use App\Domain\Financeiro\FinanceiroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContaPagarController
{
    public function index(Request $request): JsonResponse
    {
        $contas = ContaPagar::query()
            ->with(['fornecedor'])
            ->orderBy('data_vencimento', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($contas);
    }

    public function store(Request $request, FinanceiroService $service): JsonResponse
    {
        $validated = $request->validate([
            'empresa_id' => 'required|uuid',
            'fornecedor_id' => 'required|uuid',
            'numero_conta' => 'required|string',
            'descricao' => 'required|string',
            'categoria' => 'required|string',
            'valor_original' => 'required|numeric',
            'valor_total' => 'required|numeric',
            'data_vencimento' => 'required|date',
            'data_emissao' => 'required|date',
        ]);
        $conta = $service->criarContaPagar($validated);

        return response()->json($conta, Response::HTTP_CREATED);
    }

    public function show(ContaPagar $conta): JsonResponse
    {
        return response()->json($conta);
    }

    public function update(Request $request, ContaPagar $conta): JsonResponse
    {
        $conta->update($request->all());

        return response()->json($conta);
    }

    public function destroy(ContaPagar $conta): JsonResponse
    {
        $conta->delete();

        return response()->json(['message' => 'Conta a pagar excluída.']);
    }

    public function extrato(Request $request): JsonResponse
    {
        $empresaId = $request->get('empresa_id');
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');
        $contas = ContaPagar::where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->get();

        return response()->json($contas);
    }
}
