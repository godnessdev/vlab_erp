<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Models\Empresa;
use App\Models\Usuario;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

/**
 * Dashboard Principal do ERP
 *
 * Exibe estatísticas gerais, alertas e quick actions
 * Segue padrões MCP: tenant isolation, performance optimization
 */
class Index extends Component
{
    public array $estatisticas = [];

    public array $alertas = [];

    public array $atividadesRecentes = [];

    /**
     * Inicialização do componente
     *
     * ✅ Tenant context verificado via middleware
     * ✅ Performance: cache de 5 minutos
     */
    public function mount(): void
    {
        // ✅ Tenant context já definido pelo middleware SetTenantContext
        $empresa = app('current.company');

        if (! $empresa) {
            abort(403, 'Usuário não está associado a nenhuma empresa');
        }

        $this->carregarDados();
    }

    /**
     * Carrega todos os dados do dashboard
     *
     * ✅ Performance: cache por tenant
     * ✅ Tenant isolation: dados filtrados por empresa
     */
    public function carregarDados(): void
    {
        $empresa = app('current.company');

        if (! $empresa) {
            $this->estatisticas = $this->estatisticasVazias();
            $this->alertas = [];
            $this->atividadesRecentes = [];

            return;
        }

        $empresaId = $empresa->id;

        if (! $empresaId) {
            $this->estatisticas = $this->estatisticasVazias();
            $this->alertas = [];
            $this->atividadesRecentes = [];

            return;
        }

        // Cache por tenant (5 minutos)
        $cacheKey = "dashboard.{$empresaId}";

        $dados = Cache::remember($cacheKey, 300, function () use ($empresaId) {
            return [
                'estatisticas' => $this->obterEstatisticas($empresaId),
                'alertas' => $this->obterAlertas($empresaId),
                'atividades' => $this->obterAtividadesRecentes($empresaId),
            ];
        });

        $this->estatisticas = $dados['estatisticas'];
        $this->alertas = $dados['alertas'];
        $this->atividadesRecentes = $dados['atividades'];
    }

    /**
     * Obtém estatísticas gerais
     *
     * ✅ Tenant isolation: apenas dados da empresa atual
     */
    private function obterEstatisticas(string $empresaId): array
    {
        $empresa = Empresa::with(['filiais', 'usuarios'])->find($empresaId);

        if (! $empresa) {
            return $this->estatisticasVazias();
        }

        return [
            'empresa' => [
                'nome' => $empresa->nome,
                'cnpj' => $empresa->formatarCnpj(),
                'regime' => $empresa->regime_tributario->getLabel(),
                'status' => [
                    'valor' => $empresa->status->value,
                    'label' => $empresa->status->getLabel(),
                    'cor' => $empresa->status->getCor(),
                ],
            ],
            'filiais' => [
                'total' => $empresa->filiais()->count(),
                'ativas' => $empresa->filiais()->where('status', 'ATIVO')->count(),
            ],
            'usuarios' => [
                'total' => $empresa->usuarios()->count(),
                'ativos' => $empresa->usuarios()
                    ->wherePivot('status', 'ATIVO')
                    ->count(),
            ],
            // TODO: Adicionar estatísticas de outros módulos quando implementados
            'ordens_servico' => [
                'total_mes' => 0, // TODO: Implementar quando módulo OS estiver pronto
                'pendentes' => 0,
                'em_execucao' => 0,
                'concluidas' => 0,
            ],
            'faturamento' => [
                'mes_atual' => 0.00, // TODO: Implementar quando módulo faturamento estiver pronto
                'mes_anterior' => 0.00,
                'variacao_percentual' => 0.00,
            ],
            'fiscal' => [
                'nfse_emitidas_mes' => 0, // TODO: Implementar quando módulo fiscal estiver pronto
                'nfse_pendentes' => 0,
                'certificado_valido' => false,
                'dias_vencimento_certificado' => null,
            ],
        ];
    }

    /**
     * Obtém alertas importantes
     *
     * ✅ Tenant isolation: apenas alertas da empresa atual
     */
    private function obterAlertas(string $empresaId): array
    {
        $alertas = [];

        $empresa = Empresa::find($empresaId);

        if (! $empresa) {
            return $alertas;
        }

        // Alerta: Empresa inativa
        if (! $empresa->isAtiva()) {
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Empresa Inativa',
                'mensagem' => 'Sua empresa está inativa. Entre em contato com o suporte.',
                'icone' => 'exclamation-triangle',
            ];
        }

        // Alerta: Sem filiais
        if ($empresa->filiais()->count() === 0) {
            $alertas[] = [
                'tipo' => 'info',
                'titulo' => 'Cadastre uma Filial',
                'mensagem' => 'Cadastre pelo menos uma filial para começar a operar.',
                'icone' => 'information-circle',
                'acao' => [
                    'label' => 'Cadastrar Filial',
                    'url' => route('filiais.create'),
                ],
            ];
        }

        // TODO: Adicionar mais alertas quando módulos estiverem implementados
        // - Certificado digital vencendo
        // - NFS-e pendentes de envio
        // - Faturas vencidas
        // - Backup não realizado

        return $alertas;
    }

    /**
     * Obtém atividades recentes
     *
     * ✅ Tenant isolation: apenas atividades da empresa atual
     * ✅ Performance: limit 10
     */
    private function obterAtividadesRecentes(string $empresaId): array
    {
        // TODO: Implementar quando módulo de auditoria estiver pronto
        // Por enquanto, retornar array vazio

        return [
            // Exemplo de estrutura:
            // [
            //     'tipo' => 'empresa.atualizada',
            //     'descricao' => 'Empresa atualizada',
            //     'usuario' => 'João Silva',
            //     'data' => '2026-04-27 14:30:00',
            //     'icone' => 'pencil',
            // ],
        ];
    }

    /**
     * Retorna estatísticas vazias (fallback)
     */
    private function estatisticasVazias(): array
    {
        return [
            'empresa' => [
                'nome' => 'N/A',
                'cnpj' => 'N/A',
                'regime' => 'N/A',
                'status' => [
                    'valor' => 'INATIVO',
                    'label' => 'Inativo',
                    'cor' => 'danger',
                ],
            ],
            'filiais' => ['total' => 0, 'ativas' => 0],
            'usuarios' => ['total' => 0, 'ativos' => 0],
            'ordens_servico' => ['total_mes' => 0, 'pendentes' => 0, 'em_execucao' => 0, 'concluidas' => 0],
            'faturamento' => ['mes_atual' => 0.00, 'mes_anterior' => 0.00, 'variacao_percentual' => 0.00],
            'fiscal' => ['nfse_emitidas_mes' => 0, 'nfse_pendentes' => 0, 'certificado_valido' => false, 'dias_vencimento_certificado' => null],
        ];
    }

    /**
     * Atualiza os dados do dashboard
     *
     * ✅ Cache invalidation
     */
    public function atualizar(): void
    {
        $empresa = app('current.company');

        if (! $empresa) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Contexto de empresa não definido',
            ]);

            return;
        }

        $empresaId = $empresa->id;

        // Invalidar cache
        Cache::forget("dashboard.{$empresaId}");

        // Recarregar dados
        $this->carregarDados();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Dashboard atualizado com sucesso!',
        ]);
    }

    /**
     * Renderiza o componente
     */
    public function render()
    {
        return view('livewire.dashboard.index');
    }
}
