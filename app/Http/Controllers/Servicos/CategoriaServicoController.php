<?php

namespace App\Http\Controllers\Servicos;

use App\Domain\Servicos\Models\CategoriaServico;
use App\Domain\Servicos\Services\CategoriaServicoService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaServicoController extends Controller
{
    private CategoriaServicoService $categoriaService;

    public function __construct(CategoriaServicoService $categoriaService)
    {
        $this->categoriaService = $categoriaService;
    }

    /**
     * Listar categorias de serviço
     */
    public function index(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;

        $query = CategoriaServico::where('empresa_id', $empresaId)
            ->with(['servicosFilhos']);

        // Filtros
        if ($request->has('ativo')) {
            $query->where('ativo', $request->boolean('ativo'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'ilike', "%{$search}%")
                    ->orWhere('descricao', 'ilike', "%{$search}%");
            });
        }

        // Hierarquia - apenas categorias raiz se não especificado
        if ($request->has('categoria_pai_id')) {
            if ($request->categoria_pai_id === 'null' || $request->categoria_pai_id === '') {
                $query->whereNull('categoria_pai_id');
            } else {
                $query->where('categoria_pai_id', $request->categoria_pai_id);
            }
        } else {
            $query->whereNull('categoria_pai_id'); // Default: apenas raiz
        }

        $categorias = $query->orderBy('nome')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $categorias,
        ]);
    }

    /**
     * Listar todas as categorias em árvore
     */
    public function arvore(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;

        $categorias = CategoriaServico::where('empresa_id', $empresaId)
            ->whereNull('categoria_pai_id')
            ->with(['subcategorias' => function ($query) {
                $query->orderBy('nome');
            }])
            ->orderBy('nome')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categorias,
        ]);
    }

    /**
     * Exibir categoria específica
     */
    public function show(string $id): JsonResponse
    {
        $categoria = CategoriaServico::with(['categoriaPai', 'subcategorias', 'servicosFilhos'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $categoria,
        ]);
    }

    /**
     * Criar nova categoria
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string|max:500',
            'categoria_pai_id' => 'nullable|uuid|exists:categorias_servico,id',
            'cor' => 'nullable|string|max:7', // Hex color
            'icone' => 'nullable|string|max:50',
            'ordem_exibicao' => 'nullable|integer|min:0',
            'ativo' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $empresaId = $request->user()->empresa_id;

            $dados = $request->validated();
            $dados['empresa_id'] = $empresaId;

            $categoria = $this->categoriaService->criar($dados);

            return response()->json([
                'success' => true,
                'message' => 'Categoria criada com sucesso',
                'data' => $categoria->load(['categoriaPai', 'subcategorias']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Atualizar categoria
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string|max:500',
            'categoria_pai_id' => 'nullable|uuid|exists:categorias_servico,id',
            'cor' => 'nullable|string|max:7',
            'icone' => 'nullable|string|max:50',
            'ordem_exibicao' => 'nullable|integer|min:0',
            'ativo' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $categoria = $this->categoriaService->atualizar($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Categoria atualizada com sucesso',
                'data' => $categoria->load(['categoriaPai', 'subcategorias']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Excluir categoria
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->categoriaService->excluir($id);

            return response()->json([
                'success' => true,
                'message' => 'Categoria excluída com sucesso',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Ativar/Desativar categoria
     */
    public function toggleAtivo(string $id): JsonResponse
    {
        try {
            $categoria = CategoriaServico::findOrFail($id);
            $categoria->ativo = ! $categoria->ativo;
            $categoria->save();

            $status = $categoria->ativo ? 'ativada' : 'desativada';

            return response()->json([
                'success' => true,
                'message' => "Categoria {$status} com sucesso",
                'data' => $categoria,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reordenar categorias
     */
    public function reordenar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'categorias' => 'required|array',
            'categorias.*.id' => 'required|uuid|exists:categorias_servico,id',
            'categorias.*.ordem_exibicao' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            foreach ($request->categorias as $categoriaData) {
                CategoriaServico::where('id', $categoriaData['id'])
                    ->update(['ordem_exibicao' => $categoriaData['ordem_exibicao']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ordem das categorias atualizada com sucesso',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Buscar categorias para select
     */
    public function select(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;

        $categorias = CategoriaServico::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome', 'categoria_pai_id']);

        // Formatar para select hierárquico
        $options = $categorias->map(function ($categoria) use ($categorias) {
            $nivel = $this->calcularNivel($categoria, $categorias);
            $prefixo = str_repeat('— ', $nivel);

            return [
                'value' => $categoria->id,
                'label' => $prefixo.$categoria->nome,
                'nivel' => $nivel,
            ];
        })->sortBy('label')->values();

        return response()->json([
            'success' => true,
            'data' => $options,
        ]);
    }

    /**
     * Calcular nível hierárquico da categoria
     */
    private function calcularNivel(CategoriaServico $categoria, $todasCategorias, int $nivel = 0): int
    {
        if (! $categoria->categoria_pai_id) {
            return $nivel;
        }

        $pai = $todasCategorias->firstWhere('id', $categoria->categoria_pai_id);
        if (! $pai) {
            return $nivel;
        }

        return $this->calcularNivel($pai, $todasCategorias, $nivel + 1);
    }
}
