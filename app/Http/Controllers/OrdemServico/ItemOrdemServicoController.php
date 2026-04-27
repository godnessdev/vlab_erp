<?php

namespace App\Http\Controllers\OrdemServico;

use App\Http\Controllers\Controller;
use App\Domain\OrdemServico\Models\ItemOrdemServico;
use App\Domain\OrdemServico\Models\OrdemServico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ItemOrdemServicoController extends Controller
{
    /**
     * Display items for a specific service order.
     */
    public function index(OrdemServico $ordem): JsonResponse
    {
        $itens = $ordem->itens()->with('servico')->get();

        return response()->json($itens);
    }

    /**
     * Store a new item for a service order.
     */
    public function store(Request $request, OrdemServico $ordem): JsonResponse
    {
        $validated = $request->validate([
            'servico_id' => 'required|exists:servicos,id',
            'quantidade' => 'required|integer|min:1',
            'valor_unitario' => 'required|numeric|min:0',
            'descricao' => 'nullable|string',
        ]);

        $validated['ordem_servico_id'] = $ordem->id;
        $validated['valor_total'] = $validated['quantidade'] * $validated['valor_unitario'];

        $item = ItemOrdemServico::create($validated);

        return response()->json($item->load('servico'), Response::HTTP_CREATED);
    }

    /**
     * Display a specific item.
     */
    public function show(OrdemServico $ordem, ItemOrdemServico $item): JsonResponse
    {
        // Ensure the item belongs to the order
        if ($item->ordem_servico_id !== $ordem->id) {
            return response()->json([
                'message' => 'Item não pertence a esta ordem de serviço.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($item->load('servico'));
    }

    /**
     * Update a specific item.
     */
    public function update(Request $request, OrdemServico $ordem, ItemOrdemServico $item): JsonResponse
    {
        // Ensure the item belongs to the order
        if ($item->ordem_servico_id !== $ordem->id) {
            return response()->json([
                'message' => 'Item não pertence a esta ordem de serviço.',
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'servico_id' => 'sometimes|exists:servicos,id',
            'quantidade' => 'sometimes|integer|min:1',
            'valor_unitario' => 'sometimes|numeric|min:0',
            'descricao' => 'nullable|string',
        ]);

        // Recalculate total value if quantity or unit value changed
        if (isset($validated['quantidade']) || isset($validated['valor_unitario'])) {
            $quantidade = $validated['quantidade'] ?? $item->quantidade;
            $valorUnitario = $validated['valor_unitario'] ?? $item->valor_unitario;
            $validated['valor_total'] = $quantidade * $valorUnitario;
        }

        $item->update($validated);

        return response()->json($item->load('servico'));
    }

    /**
     * Remove a specific item.
     */
    public function destroy(OrdemServico $ordem, ItemOrdemServico $item): JsonResponse
    {
        // Ensure the item belongs to the order
        if ($item->ordem_servico_id !== $ordem->id) {
            return response()->json([
                'message' => 'Item não pertence a esta ordem de serviço.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Check if order allows item deletion
        if ($ordem->status === 'concluida' || $ordem->status === 'cancelada') {
            return response()->json([
                'message' => 'Não é possível remover itens de uma ordem de serviço concluída ou cancelada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $item->delete();

        return response()->json(['message' => 'Item removido com sucesso.']);
    }

    /**
     * Get total value of all items for a service order.
     */
    public function total(OrdemServico $ordem): JsonResponse
    {
        $total = $ordem->itens()->sum('valor_total');

        return response()->json([
            'ordem_servico_id' => $ordem->id,
            'total_itens' => $total,
            'quantidade_itens' => $ordem->itens()->count(),
        ]);
    }
}
