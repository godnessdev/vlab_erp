<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Bem-vindo ao {{ $estatisticas['empresa']['nome'] ?? 'ERP' }}
            </p>
        </div>
        
        <!-- Period Filter (placeholder for now) -->
        <div class="flex items-center gap-2">
            <select class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                <option value="7days">Últimos 7 dias</option>
                <option value="30days" selected>Últimos 30 dias</option>
                <option value="12months">Últimos 12 meses</option>
            </select>
            <button wire:click="atualizar" 
                    class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                Atualizar
            </button>
        </div>
    </div>

    {{-- Alertas --}}
    @if(count($alertas) > 0)
        <div class="space-y-3">
            @foreach($alertas as $alerta)
                <div class="rounded-lg border-l-4 p-4 {{ $alerta['tipo'] === 'warning' ? 'border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20' : 'border-blue-400 bg-blue-50 dark:bg-blue-900/20' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                @if($alerta['icone'] === 'exclamation-triangle')
                                    <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                @else
                                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $alerta['titulo'] }}</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $alerta['mensagem'] }}</p>
                            </div>
                        </div>
                        
                        @if(isset($alerta['acao']))
                            <a href="{{ $alerta['acao']['url'] }}" 
                               class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                                {{ $alerta['acao']['label'] }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Informações da Empresa --}}
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Empresa</p>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $estatisticas['empresa']['nome'] }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $estatisticas['empresa']['cnpj'] }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Regime Tributário</p>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $estatisticas['empresa']['regime'] }}</h2>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <div class="mt-2">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estatisticas['empresa']['status']['cor'] === 'danger' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                        {{ $estatisticas['empresa']['status']['label'] }}
                    </span>
                </div>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Filiais Ativas</p>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $estatisticas['filiais']['ativas'] }} / {{ $estatisticas['filiais']['total'] }}
                </h2>
            </div>
        </div>
    </div>

    {{-- Estatísticas Principais --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Usuários --}}
        <livewire:dashboard.stat-card
            title="Usuários Ativos"
            :value="$estatisticas['usuarios']['ativos']"
            :change="0"
            trend="neutral"
            icon="user-group"
            format="number" />

        {{-- Ordens de Serviço --}}
        <livewire:dashboard.stat-card
            title="OS do Mês"
            :value="$estatisticas['ordens_servico']['total_mes']"
            :change="0"
            trend="neutral"
            icon="clipboard-document-list"
            format="number" />

        {{-- Faturamento --}}
        <livewire:dashboard.stat-card
            title="Faturamento do Mês"
            :value="$estatisticas['faturamento']['mes_atual']"
            :change="$estatisticas['faturamento']['variacao_percentual']"
            :trend="$estatisticas['faturamento']['variacao_percentual'] > 0 ? 'up' : ($estatisticas['faturamento']['variacao_percentual'] < 0 ? 'down' : 'neutral')"
            icon="currency-dollar"
            format="currency" />

        {{-- NFS-e --}}
        <livewire:dashboard.stat-card
            title="NFS-e do Mês"
            :value="$estatisticas['fiscal']['nfse_emitidas_mes']"
            :change="0"
            trend="neutral"
            icon="document-text"
            format="number" />
    </div>

    {{-- Quick Actions --}}
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ações Rápidas</h2>
        
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <livewire:dashboard.quick-action-card
                title="Empresas"
                description="Gerenciar empresas"
                icon="building-office"
                route="empresas.ui" />

            <div class="group block rounded-lg border-2 border-dashed border-gray-300 p-6 text-center opacity-50 dark:border-gray-600">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <div class="mt-4 font-semibold text-gray-900 dark:text-gray-100">Pessoas</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Em breve</div>
            </div>

            <div class="group block rounded-lg border-2 border-dashed border-gray-300 p-6 text-center opacity-50 dark:border-gray-600">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <div class="mt-4 font-semibold text-gray-900 dark:text-gray-100">Nova OS</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Em breve</div>
            </div>

            <div class="group block rounded-lg border-2 border-dashed border-gray-300 p-6 text-center opacity-50 dark:border-gray-600">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="mt-4 font-semibold text-gray-900 dark:text-gray-100">Emitir NFS-e</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Em breve</div>
            </div>
        </div>
    </div>

    {{-- Atividades Recentes --}}
    @if(count($atividadesRecentes) > 0)
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Atividades Recentes</h2>
            
            <div class="space-y-4">
                @foreach($atividadesRecentes as $atividade)
                    <div class="flex items-start gap-4 border-b border-gray-200 pb-4 last:border-0 dark:border-gray-700">
                        <div class="rounded-full bg-gray-100 p-2 dark:bg-gray-800">
                            <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </div>
                        
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $atividade['descricao'] }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $atividade['usuario'] }} • {{ $atividade['data'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Nenhuma atividade recente</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    As atividades da sua empresa aparecerão aqui.
                </p>
            </div>
        </div>
    @endif
</div>
