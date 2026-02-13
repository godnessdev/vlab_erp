<?php

namespace App\Http\Controllers\Faturamento;

use App\Http\Controllers\Controller;
use App\Domain\Faturamento\Services\FaturaService;
use App\Domain\Faturamento\Models\Fatura;
use App\Domain\Faturamento\Enums\StatusFatura;
use App\Domain\Faturamento\Enums\TipoParcelamento;
use App\Domain\OrdemServico\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FaturaController extends Controller
{
    private FaturaService $faturaService;

    public function __construct(FaturaService $faturaService)
    {
        $this->faturaService = $faturaService;
    }

    /**
     * Listar faturas com filtros
     */
    public function index(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        
        $query = Fatura::where('empresa_id', $empresaId)->with(['cliente', 'parcelas']);

        // Aplicar filtros
        if ($request->has('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('data_inicio') && $request->has('data_fim')) {
            $query->whereBetween('data_emissao', [
                $request->data_inicio,
                $request->data_fim
            ]);
        }

        if ($request->boolean('vencidas_apenas')) {
            $query->vencidas();
        }

        // Ordenação e paginação
        $faturas = $query->orderByDesc('data_emissao')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $faturas,
            'meta' => [
                'total_valor' => $faturas->sum('valor_liquido'),
                'total_pago' => $faturas->sum(fn($f) => $f->getValorPago()),
            ]
        ]);
    }

    /**
     * Exibir detalhes de uma fatura
     */
    public function show(string $id): JsonResponse
    {
        $fatura = Fatura::with(['cliente', 'itens', 'parcelas'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'fatura' => $fatura,
                'resumo' => [
                    'valor_pago' => $fatura->getValorPago(),
                    'valor_pendente' => $fatura->getValorPendente(),
                    'percentual_pago' => $fatura->getPercentualPago(),
                    'dias_atraso' => $fatura->getDiasAtraso(),
                    'parcelas_vencidas' => $fatura->getParcelasVencidas(),
                ]
            ]
        ]);
    }

    /**
     * Criar fatura a partir de ordens de serviço
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|uuid|exists:usuarios,id',
            'ordens_servico' => 'required|array|min:1',
            'ordens_servico.*' => 'required|uuid|exists:ordem_servicos,id',
            'data_vencimento' => 'required|date|after:today',
            'mes_referencia' => 'required|date',
            'aliquota_iss' => 'required|numeric|min:0|max:100',
            'observacoes' => 'nullable|string|max:1000',
            'parcelamento' => 'required|array',
            'parcelamento.tipo' => 'required|in:A_VISTA,FIXO,VARIAVEL,PERSONALIZADO',
            'parcelamento.quantidade_parcelas' => 'required_if:parcelamento.tipo,FIXO|integer|min:1|max:12',
            'parcelamento.intervalo_dias' => 'required_if:parcelamento.tipo,FIXO|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $empresaId = $request->user()->empresa_id;
            
            // Buscar ordens de serviço
            $ordensServico = OrdemServico::whereIn('id', $request->ordens_servico)
                ->where('empresa_id', $empresaId)
                ->get();

            $dadosFatura = [
                'data_vencimento' => $request->data_vencimento,
                'mes_referencia' => $request->mes_referencia,
                'aliquota_iss' => $request->aliquota_iss,
                'observacoes' => $request->observacoes,
            ];

            $fatura = $this->faturaService->criarFaturaDeOrdens(
                $empresaId,
                $request->cliente_id,
                $ordensServico,
                $dadosFatura,
                $request->parcelamento
            );

            return response()->json([
                'success' => true,
                'message' => 'Fatura criada com sucesso',
                'data' => $fatura
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Atualizar fatura
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data_vencimento' => 'sometimes|date|after:today',
            'aliquota_iss' => 'sometimes|numeric|min:0|max:100',
            'valor_deducoes' => 'sometimes|numeric|min:0',
            'valor_descontos' => 'sometimes|numeric|min:0',
            'observacoes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $fatura = $this->faturaService->atualizarFatura($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Fatura atualizada com sucesso',
                'data' => $fatura
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Enviar fatura para o cliente
     */
    public function enviar(Request $request, string $id): JsonResponse
    {
        try {
            $fatura = $this->faturaService->enviarFatura($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Fatura enviada com sucesso',
                'data' => $fatura
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Marcar fatura como paga
     */
    public function marcarComoPaga(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data_pagamento' => 'required|date',
            'forma_pagamento' => 'required|string|max:50',
            'observacao' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $fatura = $this->faturaService->marcarComoPaga($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Fatura marcada como paga',
                'data' => $fatura
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cancelar fatura
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
            $fatura = $this->faturaService->cancelarFatura($id, $request->motivo);

            return response()->json([
                'success' => true,
                'message' => 'Fatura cancelada com sucesso',
                'data' => $fatura
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Relatório financeiro mensal
     */
    public function relatorioFinanceiro(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mes' => 'required|integer|min:1|max:12',
            'ano' => 'required|integer|min:2020|max:2030',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Período inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        $empresaId = $request->user()->empresa_id;
        $resumo = $this->faturaService->calcularResumoFinanceiro(
            $empresaId,
            $request->mes,
            $request->ano
        );

        return response()->json([
            'success' => true,
            'data' => $resumo
        ]);
    }

    /**
     * Buscar faturas vencidas
     */
    public function vencidas(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;
        $faturas = $this->faturaService->buscarFaturasVencidas($empresaId);

        return response()->json([
            'success' => true,
            'data' => $faturas,
            'meta' => [
                'total_faturas' => $faturas->count(),
                'total_valor_vencido' => $faturas->sum('valor_liquido'),
            ]
        ]);
    }

    /**
     * Buscar opções para selects
     */
    public function opcoes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'status_fatura' => StatusFatura::getOptions(),
                'tipos_parcelamento' => TipoParcelamento::getOptions(),
            ]
        ]);
    }
}
