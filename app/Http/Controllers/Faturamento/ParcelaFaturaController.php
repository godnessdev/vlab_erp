<?php

namespace App\Http\Controllers\Faturamento;

use App\Http\Controllers\Controller;
use App\Domain\Faturamento\Models\ParcelaFatura;
use App\Domain\Faturamento\Enums\StatusPagamentoParcela;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ParcelaFaturaController extends Controller
{
    /**
     * Listar parcelas com filtros
     */
    public function index(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        
        $query = ParcelaFatura::whereHas('fatura', function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId);
        })->with(['fatura', 'fatura.cliente']);

        // Aplicar filtros
        if ($request->has('fatura_id')) {
            $query->where('fatura_id', $request->fatura_id);
        }

        if ($request->has('status_pagamento')) {
            $query->where('status_pagamento', $request->status_pagamento);
        }

        if ($request->boolean('vencidas_apenas')) {
            $query->vencidas();
        }

        if ($request->boolean('proximas_vencimento')) {
            $diasAntecedencia = $request->get('dias_antecedencia', 7);
            $query->proximasAoVencimento($diasAntecedencia);
        }

        if ($request->has('data_vencimento_inicio') && $request->has('data_vencimento_fim')) {
            $query->whereBetween('data_vencimento', [
                $request->data_vencimento_inicio,
                $request->data_vencimento_fim
            ]);
        }

        // Ordenação
        $orderBy = $request->get('order_by', 'data_vencimento');
        $orderDirection = $request->get('order_direction', 'asc');
        $query->orderBy($orderBy, $orderDirection);

        $parcelas = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $parcelas,
            'meta' => [
                'total_valor_pendente' => $parcelas->where('status_pagamento', '!=', StatusPagamentoParcela::PAGO)
                    ->sum('valor_total_parcela'),
                'total_vencidas' => $parcelas->where('data_vencimento', '<', now()->toDateString())
                    ->where('status_pagamento', '!=', StatusPagamentoParcela::PAGO)
                    ->count(),
            ]
        ]);
    }

    /**
     * Exibir detalhes de uma parcela
     */
    public function show(string $id): JsonResponse
    {
        $parcela = ParcelaFatura::with(['fatura', 'fatura.cliente', 'fatura.itens'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'parcela' => $parcela,
                'calculado' => [
                    'dias_atraso' => $parcela->getDiasAtraso(),
                    'valor_com_juros_multa' => $parcela->getValorComJurosEMulta(),
                    'juros_mora' => $parcela->calcularJurosMora(),
                    'multa' => $parcela->calcularMulta(),
                    'dias_para_vencimento' => $parcela->getDiasParaVencimento(),
                ]
            ]
        ]);
    }

    /**
     * Marcar parcela como paga
     */
    public function marcarComoPaga(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'valor_pago' => 'required|numeric|min:0',
            'data_pagamento' => 'required|date',
            'forma_pagamento' => 'required|string|max:50',
            'observacoes_pagamento' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $parcela = ParcelaFatura::findOrFail($id);

            if (!$parcela->status_pagamento->podeMarcarComoPago()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parcela não pode ser marcada como paga no status atual'
                ], 400);
            }

            $parcela->marcarComoPaga(
                $request->valor_pago,
                $request->forma_pagamento,
                new \DateTime($request->data_pagamento)
            );

            if ($request->observacoes_pagamento) {
                $parcela->observacoes_pagamento = $request->observacoes_pagamento;
                $parcela->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Parcela marcada como paga',
                'data' => $parcela->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cancelar parcela
     */
    public function cancelar(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'motivo' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Motivo é obrigatório',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $parcela = ParcelaFatura::findOrFail($id);
            $parcela->cancelar();

            $parcela->observacoes_pagamento = ($parcela->observacoes_pagamento ? $parcela->observacoes_pagamento . "\n" : '') . 
                "[" . now()->format('d/m/Y H:i') . "] Cancelada: " . $request->motivo;
            $parcela->save();

            return response()->json([
                'success' => true,
                'message' => 'Parcela cancelada com sucesso',
                'data' => $parcela
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Calcular juros e multa para parcela em atraso
     */
    public function calcularJurosMulta(Request $request, string $id): JsonResponse
    {
        $parcela = ParcelaFatura::findOrFail($id);

        if (!$parcela->isVencida()) {
            return response()->json([
                'success' => false,
                'message' => 'Parcela não está vencida'
            ], 400);
        }

        $percentualJurosDia = $request->get('percentual_juros_dia', 0.03);
        $percentualMulta = $request->get('percentual_multa', 2.0);

        $calculo = [
            'valor_original' => $parcela->valor_total_parcela,
            'dias_atraso' => $parcela->getDiasAtraso(),
            'juros_mora' => $parcela->calcularJurosMora($percentualJurosDia),
            'multa' => $parcela->calcularMulta($percentualMulta),
            'valor_total_com_encargos' => $parcela->getValorComJurosEMulta($percentualJurosDia, $percentualMulta),
            'percentual_juros_aplicado' => $percentualJurosDia,
            'percentual_multa_aplicado' => $percentualMulta,
        ];

        return response()->json([
            'success' => true,
            'data' => $calculo
        ]);
    }

    /**
     * Relatório de parcelas por período
     */
    public function relatorio(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'tipo' => 'required|in:vencimento,pagamento',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parâmetros inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $empresaId = $request->user()->empresa_id;
        
        $query = ParcelaFatura::whereHas('fatura', function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId);
        })->with(['fatura', 'fatura.cliente']);

        // Filtrar por tipo de data
        if ($request->tipo === 'vencimento') {
            $query->whereBetween('data_vencimento', [
                $request->data_inicio,
                $request->data_fim
            ]);
        } else {
            $query->whereBetween('data_pagamento', [
                $request->data_inicio,
                $request->data_fim
            ])->pagas();
        }

        $parcelas = $query->get();

        // Agrupar estatísticas
        $estatisticas = [
            'total_parcelas' => $parcelas->count(),
            'valor_total' => $parcelas->sum('valor_total_parcela'),
            'valor_pago' => $parcelas->where('status_pagamento', StatusPagamentoParcela::PAGO)->sum('valor_pago'),
            'parcelas_pagas' => $parcelas->where('status_pagamento', StatusPagamentoParcela::PAGO)->count(),
            'parcelas_pendentes' => $parcelas->where('status_pagamento', StatusPagamentoParcela::PENDENTE)->count(),
            'parcelas_vencidas' => $parcelas->where('status_pagamento', StatusPagamentoParcela::ATRASADO)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'parcelas' => $parcelas,
                'estatisticas' => $estatisticas,
                'periodo' => [
                    'inicio' => $request->data_inicio,
                    'fim' => $request->data_fim,
                    'tipo' => $request->tipo,
                ]
            ]
        ]);
    }

    /**
     * Dashboard de parcelas
     */
    public function dashboard(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        
        $baseQuery = ParcelaFatura::whereHas('fatura', function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId);
        });

        $dashboard = [
            'hoje' => [
                'vencendo' => (clone $baseQuery)->where('data_vencimento', now()->toDateString())
                    ->pendentes()->count(),
                'valor_vencendo' => (clone $baseQuery)->where('data_vencimento', now()->toDateString())
                    ->pendentes()->sum('valor_total_parcela'),
            ],
            'proximos_7_dias' => [
                'vencendo' => (clone $baseQuery)->proximasAoVencimento(7)->count(),
                'valor_vencendo' => (clone $baseQuery)->proximasAoVencimento(7)->sum('valor_total_parcela'),
            ],
            'vencidas' => [
                'quantidade' => (clone $baseQuery)->vencidas()->count(),
                'valor_total' => (clone $baseQuery)->vencidas()->sum('valor_total_parcela'),
            ],
            'mes_atual' => [
                'pagas' => (clone $baseQuery)->whereMonth('data_pagamento', now()->month)
                    ->whereYear('data_pagamento', now()->year)
                    ->pagas()->count(),
                'valor_recebido' => (clone $baseQuery)->whereMonth('data_pagamento', now()->month)
                    ->whereYear('data_pagamento', now()->year)
                    ->pagas()->sum('valor_pago'),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $dashboard
        ]);
    }
}
