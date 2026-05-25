<?php

namespace App\Http\Controllers\Servicos;

use App\Domain\Servicos\Enums\ClassificacaoFiscalServico;
use App\Domain\Servicos\Enums\StatusServico;
use App\Domain\Servicos\Services\ServicoService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServicoController extends Controller
{
    public function __construct(
        private ServicoService $servicoService
    ) {}

    private function empresaId(Request $request): ?string
    {
        return tenant_id()
            ?? $request->user()?->empresa_atual_id
            ?? $request->header('X-Empresa-ID');
    }

    /**
     * Listar serviços da empresa
     */
    public function index(Request $request)
    {
        $empresaId = $this->empresaId($request);

        if (! $empresaId) {
            return response()->json([
                'error' => 'Empresa não especificada',
            ], 400);
        }

        $filtros = $request->only([
            'status',
            'classificacao_fiscal',
            'descricao',
            'unidade_medida',
            'preco_min',
            'preco_max',
        ]);

        $perPage = $request->get('per_page', 15);

        $servicos = $this->servicoService->listar($empresaId, $filtros, $perPage);

        if (! $request->expectsJson()) {
            return view('modules.list', [
                'title' => 'Servicos',
                'description' => 'Catalogo de servicos usados em ordens, faturamento e fiscal.',
                'records' => $servicos,
                'columns' => [
                    ['label' => 'Descricao', 'key' => 'descricao'],
                    ['label' => 'Unidade', 'key' => 'unidade_medida'],
                    ['label' => 'Preco base', 'key' => 'preco_base', 'type' => 'money'],
                    ['label' => 'ISS padrao', 'key' => 'aliquota_iss_default'],
                    ['label' => 'Status', 'key' => 'status'],
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $servicos->items(),
            'meta' => [
                'current_page' => $servicos->currentPage(),
                'from' => $servicos->firstItem(),
                'last_page' => $servicos->lastPage(),
                'per_page' => $servicos->perPage(),
                'to' => $servicos->lastItem(),
                'total' => $servicos->total(),
            ],
        ]);
    }

    /**
     * Criar novo serviço
     */
    public function store(Request $request): JsonResponse
    {
        $empresaId = $this->empresaId($request);

        if (! $empresaId) {
            return response()->json([
                'success' => false,
                'error' => 'Empresa não especificada',
            ], 400);
        }

        try {
            $dados = $request->all();
            $servico = $this->servicoService->criar($empresaId, $dados);

            return response()->json([
                'success' => true,
                'message' => 'Serviço criado com sucesso',
                'data' => $servico->load(['codigosMunicipais', 'regrasTributacao']),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'messages' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar serviço específico
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $empresaId = $this->empresaId($request);

        if (! $empresaId) {
            return response()->json([
                'success' => false,
                'error' => 'Empresa não especificada',
            ], 400);
        }

        try {
            $servico = $this->servicoService->buscar($empresaId, $id);

            return response()->json([
                'success' => true,
                'data' => $servico,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Serviço não encontrado',
                'messages' => $e->errors(),
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Atualizar serviço
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $empresaId = $this->empresaId($request);

        if (! $empresaId) {
            return response()->json([
                'success' => false,
                'error' => 'Empresa não especificada',
            ], 400);
        }

        try {
            $dados = $request->all();
            $servico = $this->servicoService->atualizar($empresaId, $id, $dados);

            return response()->json([
                'success' => true,
                'message' => 'Serviço atualizado com sucesso',
                'data' => $servico->load(['codigosMunicipais', 'regrasTributacao']),
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'messages' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Excluir serviço
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $empresaId = $this->empresaId($request);

        if (! $empresaId) {
            return response()->json([
                'success' => false,
                'error' => 'Empresa não especificada',
            ], 400);
        }

        try {
            $this->servicoService->excluir($empresaId, $id);

            return response()->json([
                'success' => true,
                'message' => 'Serviço excluído com sucesso',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Serviço não encontrado',
                'messages' => $e->errors(),
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ativar serviço
     */
    public function ativar(Request $request, string $id): JsonResponse
    {
        $empresaId = $this->empresaId($request);

        try {
            $servico = $this->servicoService->ativar($empresaId, $id);

            return response()->json([
                'success' => true,
                'message' => 'Serviço ativado com sucesso',
                'data' => $servico,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Serviço não encontrado',
                'messages' => $e->errors(),
            ], 404);
        }
    }

    /**
     * Inativar serviço
     */
    public function inativar(Request $request, string $id): JsonResponse
    {
        $empresaId = $this->empresaId($request);

        try {
            $servico = $this->servicoService->inativar($empresaId, $id);

            return response()->json([
                'success' => true,
                'message' => 'Serviço inativado com sucesso',
                'data' => $servico,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Serviço não encontrado',
                'messages' => $e->errors(),
            ], 404);
        }
    }

    /**
     * Buscar serviços para seleção (dropdown, etc)
     */
    public function selecao(Request $request): JsonResponse
    {
        $empresaId = $this->empresaId($request);
        $termo = $request->get('q');

        $servicos = $this->servicoService->buscarParaSelecao($empresaId, $termo);

        return response()->json([
            'success' => true,
            'data' => $servicos,
        ]);
    }

    /**
     * Calcular tributação de um serviço
     */
    public function calcularTributacao(Request $request, string $id): JsonResponse
    {
        $empresaId = $this->empresaId($request);

        $request->validate([
            'valor_base' => 'required|numeric|min:0',
            'regime_tributario' => 'required|string|in:SIMPLES_NACIONAL,LUCRO_PRESUMIDO,LUCRO_REAL,LUCRO_ARBITRADO',
            'codigo_municipio' => 'nullable|string|size:7',
        ]);

        try {
            $calculo = $this->servicoService->calcularTributacao(
                $empresaId,
                $id,
                $request->get('valor_base'),
                $request->get('regime_tributario'),
                $request->get('codigo_municipio')
            );

            return response()->json([
                'success' => true,
                'data' => $calculo,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Serviço não encontrado',
                'messages' => $e->errors(),
            ], 404);
        }
    }

    /**
     * Obter opções para selects
     */
    public function opcoes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'status_servico' => StatusServico::getOptions(),
                'classificacoes_fiscais' => ClassificacaoFiscalServico::getOptions(),
                'unidades_medida' => [
                    'UN' => 'Unidade',
                    'HR' => 'Hora',
                    'MÊS' => 'Mês',
                    'PROJ' => 'Projeto',
                    'CONS' => 'Consultoria',
                ],
            ],
        ]);
    }

    /**
     * Obter estatísticas dos serviços
     */
    public function estatisticas(Request $request): JsonResponse
    {
        $empresaId = $this->empresaId($request);

        $estatisticas = $this->servicoService->obterEstatisticas($empresaId);

        return response()->json([
            'success' => true,
            'data' => $estatisticas,
        ]);
    }
}
