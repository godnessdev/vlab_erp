<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EmpresaService;
use App\Models\RegimeTributarioEnum;
use App\Models\StatusEmpresaEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class EmpresaController extends Controller
{
    public function __construct(
        private EmpresaService $empresaService
    ) {}

    /**
     * Lista todas as empresas com filtros opcionais
     */
    public function index(Request $request): JsonResponse
    {
        $filtros = $request->only(['status', 'regime_tributario', 'busca']);
        
        $empresas = $this->empresaService->listarEmpresas($filtros);
        
        return response()->json([
            'data' => $empresas->map(function ($empresa) {
                return [
                    'id' => $empresa->id,
                    'nome' => $empresa->nome,
                    'cnpj' => $empresa->formatarCnpj(),
                    'cnpj_raw' => $empresa->cnpj,
                    'regime_tributario' => [
                        'valor' => $empresa->regime_tributario->value,
                        'label' => $empresa->regime_tributario->getLabel(),
                    ],
                    'status' => [
                        'valor' => $empresa->status->value,
                        'label' => $empresa->status->getLabel(),
                        'cor' => $empresa->status->getCor(),
                    ],
                    'data_constituicao' => $empresa->data_constituicao?->format('d/m/Y'),
                    'total_filiais' => $empresa->filiais->count(),
                    'created_at' => $empresa->created_at->format('d/m/Y H:i'),
                ];
            }),
            'meta' => [
                'total' => $empresas->count(),
            ]
        ]);
    }

    /**
     * Cria uma nova empresa
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'required|string|size:18',
            'ie' => 'nullable|string|max:20',
            'im' => 'nullable|string|max:20',
            'regime_tributario' => ['required', new Enum(RegimeTributarioEnum::class)],
            'data_constituicao' => 'nullable|date',
            'email_contato' => 'nullable|email|max:100',
            'telefone_contato' => 'nullable|string|max:20',
            'endereco_id' => 'required|uuid', // TODO: validar existência quando endereço for implementado
        ]);

        try {
            $empresa = $this->empresaService->criarEmpresa($validated);
            
            return response()->json([
                'message' => 'Empresa criada com sucesso.',
                'data' => [
                    'id' => $empresa->id,
                    'nome' => $empresa->nome,
                    'cnpj' => $empresa->formatarCnpj(),
                    'status' => $empresa->status->getLabel(),
                ]
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Exibe uma empresa específica
     */
    public function show(string $id): JsonResponse
    {
        $empresa = $this->empresaService->buscarEmpresaPorId($id);
        
        if (!$empresa) {
            return response()->json(['message' => 'Empresa não encontrada.'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $empresa->id,
                'nome' => $empresa->nome,
                'cnpj' => $empresa->formatarCnpj(),
                'cnpj_raw' => $empresa->cnpj,
                'ie' => $empresa->ie,
                'im' => $empresa->im,
                'regime_tributario' => [
                    'valor' => $empresa->regime_tributario->value,
                    'label' => $empresa->regime_tributario->getLabel(),
                    'descricao' => $empresa->regime_tributario->getDescricao(),
                ],
                'status' => [
                    'valor' => $empresa->status->value,
                    'label' => $empresa->status->getLabel(),
                    'cor' => $empresa->status->getCor(),
                ],
                'data_constituicao' => $empresa->data_constituicao?->format('d/m/Y'),
                'email_contato' => $empresa->email_contato,
                'telefone_contato' => $empresa->telefone_contato,
                'filiais' => $empresa->filiais->map(function ($filial) {
                    return [
                        'id' => $filial->id,
                        'nome' => $filial->nome,
                        'cnpj_filial' => $filial->formatarCnpj(),
                        'status' => $filial->status->getLabel(),
                    ];
                }),
                'parametros_operacionais' => $empresa->parametrosOperacionais->map(function ($parametro) {
                    return [
                        'chave' => $parametro->chave,
                        'valor' => $parametro->getValorTipado(),
                        'descricao' => $parametro->descricao,
                        'tipo' => $parametro->tipo_valor->getLabel(),
                    ];
                }),
                'created_at' => $empresa->created_at->format('d/m/Y H:i'),
                'updated_at' => $empresa->updated_at->format('d/m/Y H:i'),
            ]
        ]);
    }

    /**
     * Atualiza uma empresa
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'nome' => 'sometimes|string|max:255',
            'cnpj' => 'sometimes|string|size:18',
            'ie' => 'nullable|string|max:20',
            'im' => 'nullable|string|max:20',
            'regime_tributario' => ['sometimes', new Enum(RegimeTributarioEnum::class)],
            'data_constituicao' => 'nullable|date',
            'email_contato' => 'nullable|email|max:100',
            'telefone_contato' => 'nullable|string|max:20',
        ]);

        try {
            $empresa = $this->empresaService->atualizarEmpresa($id, $validated);
            
            return response()->json([
                'message' => 'Empresa atualizada com sucesso.',
                'data' => [
                    'id' => $empresa->id,
                    'nome' => $empresa->nome,
                    'cnpj' => $empresa->formatarCnpj(),
                    'status' => $empresa->status->getLabel(),
                ]
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Exclui uma empresa
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->empresaService->excluirEmpresa($id);
            
            return response()->json([
                'message' => 'Empresa excluída com sucesso.'
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Ativa uma empresa
     */
    public function ativar(string $id): JsonResponse
    {
        $empresa = $this->empresaService->ativarEmpresa($id);
        
        return response()->json([
            'message' => 'Empresa ativada com sucesso.',
            'data' => [
                'id' => $empresa->id,
                'status' => $empresa->status->getLabel(),
            ]
        ]);
    }

    /**
     * Inativa uma empresa
     */
    public function inativar(string $id): JsonResponse
    {
        try {
            $empresa = $this->empresaService->inativarEmpresa($id);
            
            return response()->json([
                'message' => 'Empresa inativada com sucesso.',
                'data' => [
                    'id' => $empresa->id,
                    'status' => $empresa->status->getLabel(),
                ]
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Busca empresa por CNPJ
     */
    public function buscarPorCnpj(Request $request): JsonResponse
    {
        $request->validate([
            'cnpj' => 'required|string'
        ]);

        $empresa = $this->empresaService->buscarEmpresaPorCnpj($request->cnpj);
        
        if (!$empresa) {
            return response()->json(['message' => 'Empresa não encontrada.'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $empresa->id,
                'nome' => $empresa->nome,
                'cnpj' => $empresa->formatarCnpj(),
                'status' => $empresa->status->getLabel(),
            ]
        ]);
    }

    /**
     * Obtém estatísticas das empresas
     */
    public function estatisticas(): JsonResponse
    {
        $stats = $this->empresaService->obterEstatisticas();
        
        return response()->json([
            'data' => $stats
        ]);
    }

    /**
     * Obtém opções para formulários
     */
    public function opcoes(): JsonResponse
    {
        return response()->json([
            'data' => [
                'regimes_tributarios' => RegimeTributarioEnum::getOptions(),
                'status' => StatusEmpresaEnum::getOptions(),
            ]
        ]);
    }
}
