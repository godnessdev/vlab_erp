<?php

namespace App\Domain\Servicos\Http\Controllers;

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

    /**
     * Listar serviços da empresa
     */
    public function index(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_atual_id ?? $request->header('X-Empresa-ID');
        
        if (!$empresaId) {
            return response()->json([
                'error' => 'Empresa não especificada'
            ], 400);
        }

        $filtros = $request->only([
            'status', 
            'classificacao_fiscal', 
            'descricao', 
            'unidade_medida',
            'preco_min',
            'preco_max'
        ]);

        $perPage = $request->get('per_page', 15);

        $servicos = $this->servicoService->listar($empresaId, $filtros, $perPage);

        return response()->json([
            'data' => $servicos->items(),
            'meta' => [
                'current_page' => $servicos->currentPage(),
                'from' => $servicos->firstItem(),
                'last_page' => $servicos->lastPage(),
                'per_page' => $servicos->perPage(),
                'to' => $servicos->lastItem(),
                'total' => $servicos->total(),
            ]
        ]);
    }

    /**
     * Criar novo serviço
     */
    public function store(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_atual_id ?? $request->header('X-Empresa-ID');
        
        if (!$empresaId) {
            return response()->json([
                'error' => 'Empresa não especificada'
            ], 400);
        }

        try {
            $dados = $request->all();
            $servico = $this->servicoService->criar($empresaId, $dados);

            return response()->json([
                'message' => 'Serviço criado com sucesso',
                'data' => $servico->load(['codigosMunicipais', 'regrasTributacao'])
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Dados inválidos',
                'messages' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar serviço específico
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_atual_id ?? $request->header('X-Empresa-ID');
        
        if (!$empresaId) {
            return response()->json([
                'error' => 'Empresa não especificada'
            ], 400);
        }

        try {
            $servico = $this->servicoService->buscar($empresaId, $id);

            return response()->json([
                'data' => $servico
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Serviço não encontrado',
                'messages' => $e->errors()
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar serviço
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_atual_id ?? $request->header('X-Empresa-ID');
        
        if (!$empresaId) {
            return response()->json([
                'error' => 'Empresa não especificada'
            ], 400);
        }

        try {
            $dados = $request->all();
            $servico = $this->servicoService->atualizar($empresaId, $id, $dados);

            return response()->json([
                'message' => 'Serviço atualizado com sucesso',
                'data' => $servico->load(['codigosMunicipais', 'regrasTributacao'])
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Dados inválidos',
                'messages' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir serviço
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_atual_id ?? $request->header('X-Empresa-ID');
        
        if (!$empresaId) {
            return response()->json([
                'error' => 'Empresa não especificada'
            ], 400);
        }

        try {
            $this->servicoService->excluir($empresaId, $id);

            return response()->json([
                'message' => 'Serviço excluído com sucesso'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Serviço não encontrado',
                'messages' => $e->errors()
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ativar serviço
     */
    public function ativar(Request $request, string $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_atual_id ?? $request->header('X-Empresa-ID');
        
        try {
            $servico = $this->servicoService->ativar($empresaId, $id);

            return response()->json([
                'message' => 'Serviço ativado com sucesso',
                'data' => $servico
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Serviço não encontrado',
                'messages' => $e->errors()
            ], 404);
        }
    }

    /**
     * Inativar serviço
     */
    public function inativar(Request $request, string $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_atual_id ?? $request->header('X-Empresa-ID');
        
        try {
            $servico = $this->servicoService->inativar($empresaId, $id);

            return response()->json([
                'message' => 'Serviço inativado com sucesso',
                'data' => $servico
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Serviço não encontrado',
                'messages' => $e->errors()
            ], 404);
        }
    }

    /**
     * Buscar serviços para seleção (dropdown, etc)
     */
    public function selecao(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_atual_id ?? $request->header('X-Empresa-ID');
        $termo = $request->get('q');
        
        $servicos = $this->servicoService->buscarParaSelecao($empresaId, $termo);

        return response()->json([
            'data' => $servicos
        ]);
    }

    /**
     * Calcular tributação de um serviço
     */
    public function calcularTributacao(Request $request, string $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_atual_id ?? $request->header('X-Empresa-ID');
        
        $request->validate([
            'valor_base' => 'required|numeric|min:0',
            'regime_tributario' => 'required|string|in:SIMPLES_NACIONAL,LUCRO_PRESUMIDO,LUCRO_REAL,LUCRO_ARBITRADO',
            'codigo_municipio' => 'nullable|string|size:7'
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
                'data' => $calculo
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Serviço não encontrado',
                'messages' => $e->errors()
            ], 404);
        }
    }

    /**
     * Adicionar código municipal
     */
    public function adicionarCodigoMunicipal(Request $request, string $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_atual_id ?? $request->header('X-Empresa-ID');
        
        $request->validate([
            'codigo_municipio_ibge' => 'required|string|size:7',
            'codigo_servico' => 'required|string|max:20',
            'descricao_municipal' => 'nullable|string|max:255',
            'aliquota_iss' => 'required|numeric|min:0|max:20',
            'data_vigencia_inicio' => 'required|date',
            'data_vigencia_fim' => 'nullable|date|after:data_vigencia_inicio'
        ]);

        try {
            $codigo = $this->servicoService->adicionarCodigoMunicipal(
                $empresaId, 
                $id, 
                $request->all()
            );

            return response()->json([
                'message' => 'Código municipal adicionado com sucesso',
                'data' => $codigo
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Dados inválidos',
                'messages' => $e->errors()
            ], 422);
        }
    }

    /**
     * Adicionar regra de tributação
     */
    public function adicionarRegraTributacao(Request $request, string $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_atual_id ?? $request->header('X-Empresa-ID');
        
        $request->validate([
            'regime_tributario' => 'required|string|in:SIMPLES_NACIONAL,LUCRO_PRESUMIDO,LUCRO_REAL,LUCRO_ARBITRADO',
            'aliquota_ir' => 'nullable|numeric|min:0|max:27.5',
            'aliquota_csll' => 'nullable|numeric|min:0|max:20',
            'aliquota_pis' => 'nullable|numeric|min:0|max:10',
            'aliquota_cofins' => 'nullable|numeric|min:0|max:10',
            'retencao_inss' => 'boolean',
            'base_calculo_diferenciada' => 'nullable|array',
            'regras_adicionais' => 'nullable|array'
        ]);

        try {
            $regra = $this->servicoService->adicionarRegraTributacao(
                $empresaId, 
                $id, 
                $request->all()
            );

            return response()->json([
                'message' => 'Regra de tributação adicionada com sucesso',
                'data' => $regra
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Dados inválidos',
                'messages' => $e->errors()
            ], 422);
        }
    }

    /**
     * Obter estatísticas dos serviços
     */
    public function estatisticas(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_atual_id ?? $request->header('X-Empresa-ID');
        
        $estatisticas = $this->servicoService->obterEstatisticas($empresaId);

        return response()->json([
            'data' => $estatisticas
        ]);
    }
}
