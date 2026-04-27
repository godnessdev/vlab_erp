<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StatusFilialEnum;
use App\Services\FilialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FilialController extends Controller
{
    public function __construct(
        private FilialService $filialService
    ) {}

    /**
     * Lista filiais de uma empresa
     */
    public function index(Request $request, string $empresaId): JsonResponse
    {
        $filtros = $request->only(['status', 'busca']);

        $filiais = $this->filialService->listarFiliais($empresaId, $filtros);

        return response()->json([
            'data' => $filiais->map(function ($filial) {
                return [
                    'id' => $filial->id,
                    'nome' => $filial->nome,
                    'cnpj_filial' => $filial->formatarCnpj(),
                    'status' => [
                        'valor' => $filial->status->value,
                        'label' => $filial->status->getLabel(),
                        'cor' => $filial->status->getCor(),
                    ],
                    'is_matriz' => $filial->isMatriz(),
                    'nome_completo' => $filial->getNomeCompleto(),
                    'created_at' => $filial->created_at->format('d/m/Y H:i'),
                ];
            }),
            'meta' => [
                'total' => $filiais->count(),
                'empresa_id' => $empresaId,
            ],
        ]);
    }

    /**
     * Cria uma nova filial
     */
    public function store(Request $request, string $empresaId): JsonResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj_filial' => 'nullable|string|size:18',
            'endereco_id' => 'required|uuid', // TODO: validar existência quando endereço for implementado
        ]);

        try {
            $filial = $this->filialService->criarFilial($empresaId, $validated);

            return response()->json([
                'message' => 'Filial criada com sucesso.',
                'data' => [
                    'id' => $filial->id,
                    'nome' => $filial->nome,
                    'cnpj_filial' => $filial->formatarCnpj(),
                    'status' => $filial->status->getLabel(),
                ],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Exibe uma filial específica
     */
    public function show(string $empresaId, string $id): JsonResponse
    {
        $filial = $this->filialService->buscarFilialPorId($id);

        if (! $filial || $filial->empresa_id !== $empresaId) {
            return response()->json(['message' => 'Filial não encontrada.'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $filial->id,
                'empresa_id' => $filial->empresa_id,
                'nome' => $filial->nome,
                'cnpj_filial' => $filial->formatarCnpj(),
                'cnpj_filial_raw' => $filial->cnpj_filial,
                'status' => [
                    'valor' => $filial->status->value,
                    'label' => $filial->status->getLabel(),
                    'cor' => $filial->status->getCor(),
                ],
                'is_matriz' => $filial->isMatriz(),
                'endereco_id' => $filial->endereco_id,
                'empresa' => [
                    'id' => $filial->empresa->id,
                    'nome' => $filial->empresa->nome,
                    'cnpj' => $filial->empresa->formatarCnpj(),
                ],
                'configuracoes_fiscais' => $filial->configuracoesFiscais->map(function ($config) {
                    return [
                        'id' => $config->id,
                        'codigo_municipio_ibge' => $config->codigo_municipio_ibge,
                        'aliquota_iss_default' => $config->getAliquotaFormatada(),
                        'ambiente' => $config->ambiente->getLabel(),
                    ];
                }),
                'created_at' => $filial->created_at->format('d/m/Y H:i'),
                'updated_at' => $filial->updated_at->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Atualiza uma filial
     */
    public function update(Request $request, string $empresaId, string $id): JsonResponse
    {
        $validated = $request->validate([
            'nome' => 'sometimes|string|max:255',
            'cnpj_filial' => 'nullable|string|size:18',
            'endereco_id' => 'sometimes|uuid',
        ]);

        try {
            $filial = $this->filialService->atualizarFilial($id, $validated);

            if ($filial->empresa_id !== $empresaId) {
                return response()->json(['message' => 'Filial não pertence à empresa informada.'], 422);
            }

            return response()->json([
                'message' => 'Filial atualizada com sucesso.',
                'data' => [
                    'id' => $filial->id,
                    'nome' => $filial->nome,
                    'cnpj_filial' => $filial->formatarCnpj(),
                    'status' => $filial->status->getLabel(),
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Exclui uma filial
     */
    public function destroy(string $empresaId, string $id): JsonResponse
    {
        try {
            $filial = $this->filialService->buscarFilialPorId($id);

            if (! $filial || $filial->empresa_id !== $empresaId) {
                return response()->json(['message' => 'Filial não encontrada.'], 404);
            }

            $this->filialService->excluirFilial($id);

            return response()->json([
                'message' => 'Filial excluída com sucesso.',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Ativa uma filial
     */
    public function ativar(string $empresaId, string $id): JsonResponse
    {
        $filial = $this->filialService->ativarFilial($id);

        if ($filial->empresa_id !== $empresaId) {
            return response()->json(['message' => 'Filial não pertence à empresa informada.'], 422);
        }

        return response()->json([
            'message' => 'Filial ativada com sucesso.',
            'data' => [
                'id' => $filial->id,
                'status' => $filial->status->getLabel(),
            ],
        ]);
    }

    /**
     * Inativa uma filial
     */
    public function inativar(string $empresaId, string $id): JsonResponse
    {
        try {
            $filial = $this->filialService->inativarFilial($id);

            if ($filial->empresa_id !== $empresaId) {
                return response()->json(['message' => 'Filial não pertence à empresa informada.'], 422);
            }

            return response()->json([
                'message' => 'Filial inativada com sucesso.',
                'data' => [
                    'id' => $filial->id,
                    'status' => $filial->status->getLabel(),
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtém a filial matriz
     */
    public function matriz(string $empresaId): JsonResponse
    {
        $matriz = $this->filialService->obterFilialMatriz($empresaId);

        if (! $matriz) {
            return response()->json(['message' => 'Filial matriz não encontrada.'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $matriz->id,
                'nome' => $matriz->nome,
                'cnpj_filial' => $matriz->formatarCnpj(),
                'status' => $matriz->status->getLabel(),
                'endereco_id' => $matriz->endereco_id,
            ],
        ]);
    }

    /**
     * Obtém estatísticas das filiais
     */
    public function estatisticas(string $empresaId): JsonResponse
    {
        $stats = $this->filialService->obterEstatisticas($empresaId);

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Obtém opções para formulários
     */
    public function opcoes(): JsonResponse
    {
        return response()->json([
            'data' => [
                'status' => StatusFilialEnum::getOptions(),
            ],
        ]);
    }
}
