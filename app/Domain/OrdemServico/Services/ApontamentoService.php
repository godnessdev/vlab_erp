<?php

namespace App\Domain\OrdemServico\Services;

use App\Domain\OrdemServico\Enums\TipoEventoHistorico;
use App\Domain\OrdemServico\Models\ApontamentoExecucao;
use App\Domain\OrdemServico\Models\HistoricoOrdem;
use App\Domain\OrdemServico\Models\ItemOrdemServico;
use App\Domain\OrdemServico\Models\OrdemServico;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Service para gerenciamento de Apontamentos de Execução
 */
class ApontamentoService
{
    /**
     * Registrar novo apontamento
     */
    public function registrar(array $dados, Usuario $usuario): ApontamentoExecucao
    {
        return DB::transaction(function () use ($dados, $usuario) {
            // Validar dados básicos
            $this->validarDadosApontamento($dados);

            $apontamento = ApontamentoExecucao::create([
                'ordem_servico_id' => $dados['ordem_servico_id'],
                'item_ordem_id' => $dados['item_ordem_id'],
                'usuario_id' => $usuario->id,
                'data_apontamento' => $dados['data_apontamento'] ?? now(),
                'horas_trabalhadas' => $dados['horas_trabalhadas'],
                'descricao_atividade' => $dados['descricao_atividade'],
                'observacoes' => $dados['observacoes'] ?? null,
                'aprovado' => false, // Sempre inicia como não aprovado
            ]);

            // Validar regras de negócio após criação
            if (!$apontamento->validarHoras()) {
                throw new \InvalidArgumentException('Horas inválidas para o apontamento');
            }

            if (!$apontamento->validarData()) {
                throw new \InvalidArgumentException('Data inválida para o apontamento');
            }

            // Registrar no histórico
            HistoricoOrdem::registrarApontamento(
                $apontamento->ordemServico,
                $usuario,
                $apontamento
            );

            return $apontamento->fresh(['ordemServico', 'itemOrdem', 'usuario']);
        });
    }

    /**
     * Atualizar apontamento existente
     */
    public function atualizar(
        ApontamentoExecucao $apontamento,
        array $dados,
        Usuario $usuario
    ): ApontamentoExecucao {
        // Verificar se o usuário pode editar o apontamento
        if ($apontamento->usuario_id !== $usuario->id && !$this->usuarioPodeGerenciar($usuario)) {
            throw new \UnauthorizedException('Usuário não autorizado a editar este apontamento');
        }

        // Não permitir edição de apontamentos aprovados
        if ($apontamento->aprovado) {
            throw new \InvalidArgumentException('Não é possível editar apontamento já aprovado');
        }

        return DB::transaction(function () use ($apontamento, $dados, $usuario) {
            $this->validarDadosApontamento($dados);

            $dadosOriginais = $apontamento->getOriginal();

            $apontamento->update([
                'data_apontamento' => $dados['data_apontamento'] ?? $apontamento->data_apontamento,
                'horas_trabalhadas' => $dados['horas_trabalhadas'] ?? $apontamento->horas_trabalhadas,
                'descricao_atividade' => $dados['descricao_atividade'] ?? $apontamento->descricao_atividade,
                'observacoes' => $dados['observacoes'] ?? $apontamento->observacoes,
            ]);

            // Validar após atualização
            if (!$apontamento->validarHoras()) {
                throw new \InvalidArgumentException('Horas inválidas para o apontamento');
            }

            if (!$apontamento->validarData()) {
                throw new \InvalidArgumentException('Data inválida para o apontamento');
            }

            // Registrar alteração no histórico
            $camposAlterados = [];
            foreach (['horas_trabalhadas', 'descricao_atividade', 'data_apontamento'] as $campo) {
                if ($dadosOriginais[$campo] !== $apontamento->$campo) {
                    $camposAlterados[$campo] = [
                        'old' => $dadosOriginais[$campo],
                        'new' => $apontamento->$campo
                    ];
                }
            }

            if (!empty($camposAlterados)) {
                HistoricoOrdem::create([
                    'ordem_servico_id' => $apontamento->ordem_servico_id,
                    'usuario_id' => $usuario->id,
                    'tipo_evento' => TipoEventoHistorico::EDICAO,
                    'descricao' => 'Apontamento editado',
                    'campos_alterados' => array_keys($camposAlterados),
                    'valores_anteriores' => array_column($camposAlterados, 'old', array_keys($camposAlterados)),
                    'valores_novos' => array_column($camposAlterados, 'new', array_keys($camposAlterados)),
                ]);
            }

            return $apontamento->fresh();
        });
    }

    /**
     * Aprovar apontamento
     */
    public function aprovar(ApontamentoExecucao $apontamento, Usuario $aprovador): bool
    {
        if (!$this->usuarioPodeGerenciar($aprovador)) {
            throw new \UnauthorizedException('Usuário não autorizado a aprovar apontamentos');
        }

        if ($apontamento->aprovado) {
            return true; // Já aprovado
        }

        return DB::transaction(function () use ($apontamento, $aprovador) {
            $sucesso = $apontamento->aprovar($aprovador->id);

            if ($sucesso) {
                // Registrar no histórico
                HistoricoOrdem::registrarAprovacaoApontamento(
                    $apontamento->ordemServico,
                    $aprovador,
                    $apontamento
                );

                // Auto-iniciar item se necessário
                $item = $apontamento->itemOrdem;
                if ($item->status === \App\Domain\OrdemServico\Enums\StatusItemOrdem::PENDENTE) {
                    $item->iniciar();
                }
            }

            return $sucesso;
        });
    }

    /**
     * Reprovar apontamento
     */
    public function reprovar(
        ApontamentoExecucao $apontamento,
        Usuario $reprovador,
        string $motivo = null
    ): bool {
        if (!$this->usuarioPodeGerenciar($reprovador)) {
            throw new \UnauthorizedException('Usuário não autorizado a reprovar apontamentos');
        }

        if (!$apontamento->aprovado) {
            return true; // Já reprovado
        }

        return DB::transaction(function () use ($apontamento, $reprovador, $motivo) {
            $sucesso = $apontamento->reprovar();

            if ($sucesso) {
                // Registrar no histórico
                HistoricoOrdem::create([
                    'ordem_servico_id' => $apontamento->ordem_servico_id,
                    'usuario_id' => $reprovador->id,
                    'tipo_evento' => TipoEventoHistorico::APROVACAO,
                    'descricao' => 'Apontamento reprovado' . ($motivo ? ": {$motivo}" : ''),
                    'valores_novos' => [
                        'apontamento_id' => $apontamento->id,
                        'reprovado_por' => $reprovador->nome,
                        'motivo_reprovacao' => $motivo,
                    ],
                ]);
            }

            return $sucesso;
        });
    }

    /**
     * Aprovar múltiplos apontamentos
     */
    public function aprovarLote(array $apontamentosIds, Usuario $aprovador): array
    {
        $resultado = [
            'aprovados' => 0,
            'falhas' => 0,
            'erros' => []
        ];

        foreach ($apontamentosIds as $id) {
            try {
                $apontamento = ApontamentoExecucao::findOrFail($id);
                $this->aprovar($apontamento, $aprovador);
                $resultado['aprovados']++;
            } catch (\Exception $e) {
                $resultado['falhas']++;
                $resultado['erros'][$id] = $e->getMessage();
            }
        }

        return $resultado;
    }

    /**
     * Buscar apontamentos com filtros
     */
    public function buscar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $query = ApontamentoExecucao::with(['ordemServico', 'itemOrdem', 'usuario', 'aprovadoPor'])
            ->orderBy('data_apontamento', 'desc');

        // Aplicar filtros
        if (!empty($filtros['usuario_id'])) {
            $query->where('usuario_id', $filtros['usuario_id']);
        }

        if (!empty($filtros['ordem_servico_id'])) {
            $query->where('ordem_servico_id', $filtros['ordem_servico_id']);
        }

        if (!empty($filtros['item_ordem_id'])) {
            $query->where('item_ordem_id', $filtros['item_ordem_id']);
        }

        if (isset($filtros['aprovado'])) {
            $query->where('aprovado', $filtros['aprovado']);
        }

        if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
            $query->whereBetween('data_apontamento', [$filtros['data_inicio'], $filtros['data_fim']]);
        }

        if (!empty($filtros['empresa_id'])) {
            $query->whereHas('ordemServico', function ($q) use ($filtros) {
                $q->where('empresa_id', $filtros['empresa_id']);
            });
        }

        // Filtros de período predefinidos
        if (!empty($filtros['periodo'])) {
            match ($filtros['periodo']) {
                'hoje' => $query->hoje(),
                'semana' => $query->essaSemana(),
                'mes' => $query->esseMes(),
                default => null,
            };
        }

        return $query->paginate($porPagina);
    }

    /**
     * Obter apontamentos pendentes de aprovação
     */
    public function obterPendentesAprovacao(string $empresaId): Collection
    {
        return ApontamentoExecucao::with(['ordemServico', 'itemOrdem', 'usuario'])
            ->whereHas('ordemServico', function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId);
            })
            ->pendentesAprovacao()
            ->orderBy('data_apontamento', 'desc')
            ->get();
    }

    /**
     * Relatório de horas trabalhadas
     */
    public function relatorioHoras(array $filtros): array
    {
        $query = ApontamentoExecucao::with(['usuario', 'ordemServico'])
            ->aprovados();

        // Aplicar filtros
        if (!empty($filtros['empresa_id'])) {
            $query->whereHas('ordemServico', function ($q) use ($filtros) {
                $q->where('empresa_id', $filtros['empresa_id']);
            });
        }

        if (!empty($filtros['usuario_id'])) {
            $query->where('usuario_id', $filtros['usuario_id']);
        }

        if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
            $query->whereBetween('data_apontamento', [$filtros['data_inicio'], $filtros['data_fim']]);
        }

        $apontamentos = $query->get();

        // Agrupar por usuário
        $relatorio = [
            'periodo' => [
                'inicio' => $filtros['data_inicio'] ?? null,
                'fim' => $filtros['data_fim'] ?? null,
            ],
            'resumo' => [
                'total_horas' => $apontamentos->sum('horas_trabalhadas'),
                'total_apontamentos' => $apontamentos->count(),
                'usuarios_envolvidos' => $apontamentos->pluck('usuario_id')->unique()->count(),
            ],
            'por_usuario' => [],
        ];

        foreach ($apontamentos->groupBy('usuario_id') as $usuarioId => $apontamentosUsuario) {
            $usuario = $apontamentosUsuario->first()->usuario;
            
            $relatorio['por_usuario'][$usuarioId] = [
                'nome' => $usuario->nome ?? 'Usuário não encontrado',
                'email' => $usuario->email ?? '',
                'total_horas' => $apontamentosUsuario->sum('horas_trabalhadas'),
                'total_apontamentos' => $apontamentosUsuario->count(),
                'horas_por_dia' => $apontamentosUsuario->groupBy(function($item) {
                    return $item->data_apontamento->format('Y-m-d');
                })->map(function($grupo) {
                    return $grupo->sum('horas_trabalhadas');
                })->toArray(),
            ];
        }

        return $relatorio;
    }

    /**
     * Excluir apontamento
     */
    public function excluir(ApontamentoExecucao $apontamento, Usuario $usuario): bool
    {
        // Verificar permissões
        if ($apontamento->usuario_id !== $usuario->id && !$this->usuarioPodeGerenciar($usuario)) {
            throw new \UnauthorizedException('Usuário não autorizado a excluir este apontamento');
        }

        // Não permitir exclusão de apontamentos aprovados
        if ($apontamento->aprovado) {
            throw new \InvalidArgumentException('Não é possível excluir apontamento já aprovado');
        }

        return DB::transaction(function () use ($apontamento, $usuario) {
            // Registrar exclusão no histórico antes de deletar
            HistoricoOrdem::create([
                'ordem_servico_id' => $apontamento->ordem_servico_id,
                'usuario_id' => $usuario->id,
                'tipo_evento' => TipoEventoHistorico::EDICAO,
                'descricao' => 'Apontamento removido',
                'valores_anteriores' => $apontamento->toArray(),
            ]);

            return $apontamento->delete();
        });
    }

    /**
     * Validar dados do apontamento
     */
    private function validarDadosApontamento(array $dados): void
    {
        if (empty($dados['ordem_servico_id'])) {
            throw new \InvalidArgumentException('ID da ordem de serviço é obrigatório');
        }

        if (empty($dados['item_ordem_id'])) {
            throw new \InvalidArgumentException('ID do item da ordem é obrigatório');
        }

        if (empty($dados['horas_trabalhadas']) || $dados['horas_trabalhadas'] <= 0) {
            throw new \InvalidArgumentException('Horas trabalhadas deve ser maior que zero');
        }

        if ($dados['horas_trabalhadas'] > 24) {
            throw new \InvalidArgumentException('Horas trabalhadas não pode ser maior que 24h por dia');
        }

        if (empty($dados['descricao_atividade'])) {
            throw new \InvalidArgumentException('Descrição da atividade é obrigatória');
        }

        // Validar se ordem e item existem e são compatíveis
        $ordem = OrdemServico::find($dados['ordem_servico_id']);
        if (!$ordem) {
            throw new \InvalidArgumentException('Ordem de serviço não encontrada');
        }

        $item = ItemOrdemServico::find($dados['item_ordem_id']);
        if (!$item || $item->ordem_servico_id !== $dados['ordem_servico_id']) {
            throw new \InvalidArgumentException('Item não pertence à ordem de serviço informada');
        }
    }

    /**
     * Verificar se usuário pode gerenciar apontamentos (aprovar/reprovar)
     */
    private function usuarioPodeGerenciar(Usuario $usuario): bool
    {
        // Implementar lógica de permissões baseada em papéis
        // Por enquanto, assumindo que todos podem gerenciar
        return true;
    }
}
