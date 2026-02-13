<?php

namespace App\Domain\OrdemServico\Services;

use App\Domain\OrdemServico\Enums\StatusOrdemServico;
use App\Domain\OrdemServico\Enums\TipoEventoHistorico;
use App\Domain\OrdemServico\Models\HistoricoOrdem;
use App\Domain\OrdemServico\Models\OrdemServico;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Service para gerenciamento de Ordens de Serviço
 */
class OrdemServicoService
{
    /**
     * Criar nova ordem de serviço
     */
    public function criar(array $dados, Usuario $usuario): OrdemServico
    {
        return DB::transaction(function () use ($dados, $usuario) {
            $ordem = OrdemServico::create([
                'empresa_id' => $dados['empresa_id'],
                'cliente_id' => $dados['cliente_id'],
                'titulo' => $dados['titulo'],
                'descricao' => $dados['descricao'] ?? null,
                'data_prevista_inicio' => $dados['data_prevista_inicio'] ?? null,
                'data_prevista_conclusao' => $dados['data_prevista_conclusao'] ?? null,
                'prioridade' => $dados['prioridade'] ?? \App\Domain\OrdemServico\Enums\PrioridadeOrdem::NORMAL,
                'status' => StatusOrdemServico::ABERTA,
                'observacoes' => $dados['observacoes'] ?? null,
            ]);

            // Registrar no histórico
            HistoricoOrdem::registrarCriacao($ordem, $usuario);

            // Adicionar itens se fornecidos
            if (isset($dados['itens']) && is_array($dados['itens'])) {
                foreach ($dados['itens'] as $dadosItem) {
                    $this->adicionarItem($ordem, $dadosItem, $usuario);
                }
            }

            return $ordem->fresh(['itens', 'cliente', 'empresa']);
        });
    }

    /**
     * Atualizar ordem de serviço
     */
    public function atualizar(OrdemServico $ordem, array $dados, Usuario $usuario): OrdemServico
    {
        return DB::transaction(function () use ($ordem, $dados, $usuario) {
            $camposAlterados = [];
            $dadosOriginais = $ordem->getOriginal();

            // Campos que podem ser alterados
            $camposPermitidos = [
                'titulo', 'descricao', 'data_prevista_inicio', 
                'data_prevista_conclusao', 'prioridade', 'observacoes'
            ];

            foreach ($camposPermitidos as $campo) {
                if (isset($dados[$campo]) && $dados[$campo] !== $dadosOriginais[$campo]) {
                    $camposAlterados[$campo] = [
                        'old' => $dadosOriginais[$campo],
                        'new' => $dados[$campo]
                    ];
                }
            }

            if (!empty($camposAlterados)) {
                $ordem->update(array_intersect_key($dados, array_flip($camposPermitidos)));
                
                HistoricoOrdem::registrarEdicao($ordem, $usuario, $camposAlterados);
            }

            return $ordem->fresh();
        });
    }

    /**
     * Adicionar item à ordem
     */
    public function adicionarItem(OrdemServico $ordem, array $dadosItem, Usuario $usuario): void
    {
        DB::transaction(function () use ($ordem, $dadosItem, $usuario) {
            $item = $ordem->itens()->create([
                'servico_id' => $dadosItem['servico_id'],
                'descricao' => $dadosItem['descricao'] ?? null,
                'quantidade' => $dadosItem['quantidade'],
                'preco_unitario' => $dadosItem['preco_unitario'],
                'observacoes' => $dadosItem['observacoes'] ?? null,
            ]);

            HistoricoOrdem::create([
                'ordem_servico_id' => $ordem->id,
                'usuario_id' => $usuario->id,
                'tipo_evento' => TipoEventoHistorico::EDICAO,
                'descricao' => "Item adicionado: {$item->descricao}",
                'valores_novos' => $item->toArray(),
            ]);
        });
    }

    /**
     * Remover item da ordem
     */
    public function removerItem(OrdemServico $ordem, string $itemId, Usuario $usuario): void
    {
        DB::transaction(function () use ($ordem, $itemId, $usuario) {
            $item = $ordem->itens()->findOrFail($itemId);
            
            // Não permitir remoção se há apontamentos aprovados
            if ($item->apontamentos()->where('aprovado', true)->exists()) {
                throw new \InvalidArgumentException('Não é possível remover item com apontamentos aprovados');
            }

            $dadosItem = $item->toArray();
            $item->delete();

            HistoricoOrdem::create([
                'ordem_servico_id' => $ordem->id,
                'usuario_id' => $usuario->id,
                'tipo_evento' => TipoEventoHistorico::EDICAO,
                'descricao' => "Item removido: {$dadosItem['descricao']}",
                'valores_anteriores' => $dadosItem,
            ]);
        });
    }

    /**
     * Alterar status da ordem
     */
    public function alterarStatus(
        OrdemServico $ordem,
        StatusOrdemServico $novoStatus,
        Usuario $usuario,
        string $observacao = null
    ): bool {
        if (!$ordem->podeTransicionarPara($novoStatus)) {
            throw new \InvalidArgumentException("Não é possível alterar status de {$ordem->status->getLabel()} para {$novoStatus->getLabel()}");
        }

        return DB::transaction(function () use ($ordem, $novoStatus, $usuario, $observacao) {
            $statusAnterior = $ordem->status;

            // Executar mudança de status específica
            $sucesso = match ($novoStatus) {
                StatusOrdemServico::EM_ANDAMENTO => $ordem->iniciar(),
                StatusOrdemServico::PAUSADA => $ordem->pausar(),
                StatusOrdemServico::CONCLUIDA => $ordem->concluir(),
                StatusOrdemServico::CANCELADA => $ordem->cancelar(),
                StatusOrdemServico::FATURADA => $ordem->faturar(),
                default => false,
            };

            if ($sucesso) {
                // Registrar no histórico
                if ($novoStatus === StatusOrdemServico::CANCELADA) {
                    HistoricoOrdem::registrarCancelamento($ordem, $usuario, $observacao);
                } else {
                    HistoricoOrdem::registrarMudancaStatus($ordem, $usuario, $statusAnterior, $novoStatus);
                }

                if ($observacao) {
                    HistoricoOrdem::registrarComentario($ordem, $usuario, $observacao);
                }
            }

            return $sucesso;
        });
    }

    /**
     * Buscar ordens com filtros
     */
    public function buscar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $query = OrdemServico::with(['cliente', 'empresa', 'itens'])
            ->orderBy('created_at', 'desc');

        // Aplicar filtros
        if (!empty($filtros['empresa_id'])) {
            $query->where('empresa_id', $filtros['empresa_id']);
        }

        if (!empty($filtros['cliente_id'])) {
            $query->where('cliente_id', $filtros['cliente_id']);
        }

        if (!empty($filtros['status'])) {
            if (is_array($filtros['status'])) {
                $query->whereIn('status', $filtros['status']);
            } else {
                $query->where('status', $filtros['status']);
            }
        }

        if (!empty($filtros['prioridade'])) {
            $query->where('prioridade', $filtros['prioridade']);
        }

        if (!empty($filtros['numero_ordem'])) {
            $query->where('numero_ordem', 'LIKE', "%{$filtros['numero_ordem']}%");
        }

        if (!empty($filtros['titulo'])) {
            $query->where('titulo', 'LIKE', "%{$filtros['titulo']}%");
        }

        if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
            $query->whereBetween('data_abertura', [$filtros['data_inicio'], $filtros['data_fim']]);
        }

        if (isset($filtros['em_atraso']) && $filtros['em_atraso']) {
            $query->emAtraso();
        }

        if (isset($filtros['ativas']) && $filtros['ativas']) {
            $query->ativas();
        }

        return $query->paginate($porPagina);
    }

    /**
     * Obter dashboard de estatísticas
     */
    public function obterEstatisticas(string $empresaId, array $filtros = []): array
    {
        $query = OrdemServico::where('empresa_id', $empresaId);

        // Aplicar filtro de período se fornecido
        if (!empty($filtros['periodo_inicio']) && !empty($filtros['periodo_fim'])) {
            $query->whereBetween('created_at', [$filtros['periodo_inicio'], $filtros['periodo_fim']]);
        }

        $estatisticas = [
            'total_ordens' => $query->count(),
            'ordens_abertas' => $query->porStatus(StatusOrdemServico::ABERTA)->count(),
            'ordens_em_andamento' => $query->porStatus(StatusOrdemServico::EM_ANDAMENTO)->count(),
            'ordens_pausadas' => $query->porStatus(StatusOrdemServico::PAUSADA)->count(),
            'ordens_concluidas' => $query->porStatus(StatusOrdemServico::CONCLUIDA)->count(),
            'ordens_faturadas' => $query->porStatus(StatusOrdemServico::FATURADA)->count(),
            'ordens_canceladas' => $query->porStatus(StatusOrdemServico::CANCELADA)->count(),
            'ordens_em_atraso' => $query->emAtraso()->count(),
            'valor_total_estimado' => $query->sum('valor_total_estimado'),
            'valor_total_executado' => $query->sum('valor_total_executado'),
        ];

        // Distribuição por prioridade
        $estatisticas['por_prioridade'] = $query->select('prioridade', DB::raw('count(*) as total'))
            ->groupBy('prioridade')
            ->pluck('total', 'prioridade')
            ->toArray();

        // Ordens por mês (últimos 12 meses)
        $estatisticas['por_mes'] = $query->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        return $estatisticas;
    }

    /**
     * Duplicar ordem de serviço
     */
    public function duplicar(OrdemServico $ordemOriginal, array $novosDados, Usuario $usuario): OrdemServico
    {
        return DB::transaction(function () use ($ordemOriginal, $novosDados, $usuario) {
            $dadosOrdem = $ordemOriginal->toArray();
            
            // Remover campos que não devem ser duplicados
            unset($dadosOrdem['id'], $dadosOrdem['numero_ordem'], $dadosOrdem['created_at'], 
                  $dadosOrdem['updated_at'], $dadosOrdem['deleted_at'], $dadosOrdem['data_inicio_real'],
                  $dadosOrdem['data_conclusao_real'], $dadosOrdem['valor_total_executado']);

            // Aplicar novos dados
            $dadosOrdem = array_merge($dadosOrdem, $novosDados);
            $dadosOrdem['status'] = StatusOrdemServico::ABERTA;
            $dadosOrdem['titulo'] = '[CÓPIA] ' . $dadosOrdem['titulo'];

            $novaOrdem = OrdemServico::create($dadosOrdem);

            // Duplicar itens
            foreach ($ordemOriginal->itens as $itemOriginal) {
                $dadosItem = $itemOriginal->toArray();
                unset($dadosItem['id'], $dadosItem['ordem_servico_id'], $dadosItem['created_at'],
                      $dadosItem['updated_at'], $dadosItem['deleted_at'], $dadosItem['quantidade_executada'],
                      $dadosItem['data_inicio'], $dadosItem['data_conclusao']);

                $dadosItem['status'] = \App\Domain\OrdemServico\Enums\StatusItemOrdem::PENDENTE;
                $novaOrdem->itens()->create($dadosItem);
            }

            HistoricoOrdem::create([
                'ordem_servico_id' => $novaOrdem->id,
                'usuario_id' => $usuario->id,
                'tipo_evento' => TipoEventoHistorico::CRIACAO,
                'descricao' => "Ordem criada como cópia da OS: {$ordemOriginal->numero_ordem}",
                'valores_novos' => ['ordem_original_id' => $ordemOriginal->id],
            ]);

            return $novaOrdem->fresh(['itens', 'cliente', 'empresa']);
        });
    }

    /**
     * Obter próximas ordens por vencimento
     */
    public function obterProximasAVencer(string $empresaId, int $diasAviso = 7): Collection
    {
        return OrdemServico::where('empresa_id', $empresaId)
            ->ativas()
            ->where('data_prevista_conclusao', '<=', now()->addDays($diasAviso))
            ->where('data_prevista_conclusao', '>=', now())
            ->orderBy('data_prevista_conclusao')
            ->with(['cliente', 'itens'])
            ->get();
    }

    /**
     * Relatório de produtividade
     */
    public function relatorioProdutividade(string $empresaId, \Carbon\Carbon $inicio, \Carbon\Carbon $fim): array
    {
        $ordens = OrdemServico::where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$inicio, $fim])
            ->with(['apontamentos.usuario', 'itens'])
            ->get();

        $relatorio = [
            'periodo' => [
                'inicio' => $inicio->format('Y-m-d'),
                'fim' => $fim->format('Y-m-d'),
            ],
            'resumo' => [
                'total_ordens' => $ordens->count(),
                'ordens_concluidas' => $ordens->where('status', StatusOrdemServico::CONCLUIDA)->count(),
                'total_horas' => $ordens->sum(fn($ordem) => $ordem->calcularHorasTrabalhadas()),
                'valor_faturado' => $ordens->whereIn('status', [StatusOrdemServico::FATURADA])->sum('valor_total_executado'),
            ],
            'por_usuario' => [],
        ];

        // Agrupar por usuário
        foreach ($ordens as $ordem) {
            foreach ($ordem->apontamentos as $apontamento) {
                $usuarioId = $apontamento->usuario_id;
                $nomeUsuario = $apontamento->usuario->nome ?? 'Usuário não encontrado';

                if (!isset($relatorio['por_usuario'][$usuarioId])) {
                    $relatorio['por_usuario'][$usuarioId] = [
                        'nome' => $nomeUsuario,
                        'horas_trabalhadas' => 0,
                        'apontamentos' => 0,
                        'ordens_trabalhadas' => [],
                    ];
                }

                $relatorio['por_usuario'][$usuarioId]['horas_trabalhadas'] += $apontamento->horas_trabalhadas;
                $relatorio['por_usuario'][$usuarioId]['apontamentos']++;
                
                if (!in_array($ordem->id, $relatorio['por_usuario'][$usuarioId]['ordens_trabalhadas'])) {
                    $relatorio['por_usuario'][$usuarioId]['ordens_trabalhadas'][] = $ordem->id;
                }
            }
        }

        return $relatorio;
    }
}
