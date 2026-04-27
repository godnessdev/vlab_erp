<?php

namespace App\Http\Controllers\Financeiro;

use App\Domain\Financeiro\FinanceiroService;
use App\Domain\Financeiro\Recebimento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RecebimentoController
{
    public function index(Request $request): JsonResponse
    {
        $recebimentos = Recebimento::query()
            ->orderBy('data_recebimento', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($recebimentos);
    }

    public function store(Request $request, FinanceiroService $service): JsonResponse
    {
        $validated = $request->validate([
            'conta_receber_id' => 'required|uuid',
            'data_recebimento' => 'required|date',
            'valor_recebido' => 'required|numeric',
            'forma_recebimento' => 'required|string',
        ]);
        $recebimento = $service->registrarRecebimento($validated);

        return response()->json($recebimento, Response::HTTP_CREATED);
    }

    public function show(Recebimento $recebimento): JsonResponse
    {
        return response()->json($recebimento);
    }

    public function update(Request $request, Recebimento $recebimento): JsonResponse
    {
        $recebimento->update($request->all());

        return response()->json($recebimento);
    }

    public function destroy(Recebimento $recebimento): JsonResponse
    {
        $recebimento->delete();

        return response()->json(['message' => 'Recebimento excluído.']);
    }
}
