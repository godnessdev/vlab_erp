<?php

namespace App\Http\Controllers\OrdemServico;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico\OrdemServico;
use App\Services\OrdemServico\OrdemServicoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrdemServicoController extends Controller
{
    public function __construct(
        private readonly OrdemServicoService $ordemServicoService
    ) {}

    /**
     * Display a listing of service orders.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        $clienteId = $request->get('cliente_id');

        $query = OrdemServico::with(['cliente', 'servico', 'responsavel', 'equipe', 'itens.servico']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        $ordens = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($ordens);
    }

    /**
     * Store a newly created service order.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'servico_id' => 'required|exists:servicos,id',
            'responsavel_id' => 'required|exists:users,id',
            'equipe_id' => 'nullable|exists:equipes,id',
            'descricao' => 'required|string|max:1000',
            'observacoes' => 'nullable|string',
            'prioridade' => 'required|in:baixa,normal,alta,critica',
            'categoria' => 'required|in:manutencao,instalacao,consultoria,suporte',
            'data_prevista' => 'nullable|date',
            'itens' => 'nullable|array',
            'itens.*.servico_id' => 'required_with:itens|exists:servicos,id',
            'itens.*.quantidade' => 'required_with:itens|integer|min:1',
            'itens.*.valor_unitario' => 'required_with:itens|numeric|min:0',
            'itens.*.descricao' => 'nullable|string',
        ]);

        $ordem = $this->ordemServicoService->criarOrdem($validated);

        return response()->json($ordem->load([
            'cliente',
            'servico',
            'responsavel',
            'equipe',
            'itens.servico',
        ]), Response::HTTP_CREATED);
    }

    /**
     * Display the specified service order.
     */
    public function show(OrdemServico $ordem): JsonResponse
    {
        return response()->json($ordem->load([
            'cliente',
            'servico',
            'responsavel',
            'equipe',
            'itens.servico',
        ]));
    }

    /**
     * Update the specified service order.
     */
    public function update(Request $request, OrdemServico $ordem): JsonResponse
    {
        $validated = $request->validate([
            'servico_id' => 'sometimes|exists:servicos,id',
            'responsavel_id' => 'sometimes|exists:users,id',
            'equipe_id' => 'nullable|exists:equipes,id',
            'descricao' => 'sometimes|string|max:1000',
            'observacoes' => 'nullable|string',
            'prioridade' => 'sometimes|in:baixa,normal,alta,critica',
            'categoria' => 'sometimes|in:manutencao,instalacao,consultoria,suporte',
            'data_prevista' => 'nullable|date',
            'data_inicio' => 'nullable|date',
            'data_conclusao' => 'nullable|date',
        ]);

        $ordem->update($validated);

        return response()->json($ordem->load([
            'cliente',
            'servico',
            'responsavel',
            'equipe',
            'itens.servico',
        ]));
    }

    /**
     * Remove the specified service order.
     */
    public function destroy(OrdemServico $ordem): JsonResponse
    {
        if ($ordem->status !== 'aberta') {
            return response()->json([
                'message' => 'Apenas ordens de serviço em status "aberta" podem ser excluídas.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $ordem->delete();

        return response()->json(['message' => 'Ordem de serviço excluída com sucesso.']);
    }

    /**
     * Update service order status.
     */
    public function updateStatus(Request $request, OrdemServico $ordem): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:aberta,em_andamento,pausada,concluida,cancelada',
            'observacoes' => 'nullable|string',
        ]);

        $ordem = $this->ordemServicoService->atualizarStatus($ordem, $validated['status'], $validated['observacoes'] ?? null);

        return response()->json($ordem->load([
            'cliente',
            'servico',
            'responsavel',
            'equipe',
            'itens.servico',
        ]));
    }

    /**
     * Assign team to service order.
     */
    public function assignTeam(Request $request, OrdemServico $ordem): JsonResponse
    {
        $validated = $request->validate([
            'equipe_id' => 'required|exists:equipes,id',
        ]);

        $ordem->update(['equipe_id' => $validated['equipe_id']]);

        return response()->json($ordem->load([
            'cliente',
            'servico',
            'responsavel',
            'equipe',
            'itens.servico',
        ]));
    }

    /**
     * Get service orders by status.
     */
    public function byStatus(Request $request, string $status): JsonResponse
    {
        $perPage = $request->get('per_page', 15);

        $ordens = OrdemServico::with(['cliente', 'servico', 'responsavel', 'equipe', 'itens.servico'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($ordens);
    }

    /**
     * Get service orders statistics.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total' => OrdemServico::count(),
            'abertas' => OrdemServico::where('status', 'aberta')->count(),
            'em_andamento' => OrdemServico::where('status', 'em_andamento')->count(),
            'pausadas' => OrdemServico::where('status', 'pausada')->count(),
            'concluidas' => OrdemServico::where('status', 'concluida')->count(),
            'canceladas' => OrdemServico::where('status', 'cancelada')->count(),
            'por_prioridade' => [
                'baixa' => OrdemServico::where('prioridade', 'baixa')->count(),
                'normal' => OrdemServico::where('prioridade', 'normal')->count(),
                'alta' => OrdemServico::where('prioridade', 'alta')->count(),
                'critica' => OrdemServico::where('prioridade', 'critica')->count(),
            ],
            'por_categoria' => [
                'manutencao' => OrdemServico::where('categoria', 'manutencao')->count(),
                'instalacao' => OrdemServico::where('categoria', 'instalacao')->count(),
                'consultoria' => OrdemServico::where('categoria', 'consultoria')->count(),
                'suporte' => OrdemServico::where('categoria', 'suporte')->count(),
            ],
        ];

        return response()->json($stats);
    }
}
