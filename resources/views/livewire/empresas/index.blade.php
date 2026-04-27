<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Gestão de Empresas</flux:heading>
            <flux:subheading>Gerencie as empresas cadastradas no sistema</flux:subheading>
        </div>
        
        <flux:button wire:click="abrirModal" icon="plus" variant="primary">
            Nova Empresa
        </flux:button>
    </div>

    {{-- Estatísticas --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <flux:text class="text-sm text-gray-500">Total de Empresas</flux:text>
                    <flux:heading size="lg">{{ $estatisticas['total_empresas'] ?? 0 }}</flux:heading>
                </div>
                <div class="rounded-full bg-blue-100 p-3">
                    <flux:icon.building-office class="h-6 w-6 text-blue-600" />
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <flux:text class="text-sm text-gray-500">Empresas Ativas</flux:text>
                    <flux:heading size="lg">{{ $estatisticas['empresas_ativas'] ?? 0 }}</flux:heading>
                </div>
                <div class="rounded-full bg-green-100 p-3">
                    <flux:icon.check-circle class="h-6 w-6 text-green-600" />
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <flux:text class="text-sm text-gray-500">Empresas Inativas</flux:text>
                    <flux:heading size="lg">{{ $estatisticas['empresas_inativas'] ?? 0 }}</flux:heading>
                </div>
                <div class="rounded-full bg-red-100 p-3">
                    <flux:icon.x-circle class="h-6 w-6 text-red-600" />
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <flux:text class="text-sm text-gray-500">Total de Filiais</flux:text>
                    <flux:heading size="lg">{{ $estatisticas['total_filiais'] ?? 0 }}</flux:heading>
                </div>
                <div class="rounded-full bg-purple-100 p-3">
                    <flux:icon.map-pin class="h-6 w-6 text-purple-600" />
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Filtros --}}
    <flux:card>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <flux:input 
                    wire:model.live.debounce.300ms="busca" 
                    placeholder="Buscar por nome ou CNPJ..."
                    icon="magnifying-glass"
                />
            </div>

            <div>
                <flux:select wire:model.live="status" placeholder="Todos os status">
                    <option value="">Todos os status</option>
                    @foreach($statusOptions as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <flux:select wire:model.live="regime_tributario" placeholder="Todos os regimes">
                    <option value="">Todos os regimes</option>
                    @foreach($regimes as $regime)
                        <option value="{{ $regime['value'] }}">{{ $regime['label'] }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        @if($busca || $status || $regime_tributario)
            <div class="mt-4">
                <flux:button wire:click="limparFiltros" variant="ghost" size="sm" icon="x-mark">
                    Limpar filtros
                </flux:button>
            </div>
        @endif
    </flux:card>

    {{-- Tabela de Empresas --}}
    <flux:card>
        @if($empresas->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Empresa
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                CNPJ
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Regime Tributário
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Filiais
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Data Cadastro
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        @foreach($empresas as $empresa)
                            <tr wire:key="empresa-{{ $empresa['id'] }}" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $empresa['nome'] }}
                                        </div>
                                        @if($empresa['data_constituicao'])
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Constituída em {{ $empresa['data_constituicao'] }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="font-mono text-sm text-gray-900 dark:text-gray-100">
                                        {{ $empresa['cnpj'] }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4">
                                    <flux:badge variant="outline">
                                        {{ $empresa['regime_tributario']['label'] }}
                                    </flux:badge>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4">
                                    <flux:badge :variant="$empresa['status']['cor']">
                                        {{ $empresa['status']['label'] }}
                                    </flux:badge>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-gray-900 dark:text-gray-100">
                                    {{ $empresa['total_filiais'] }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $empresa['created_at'] }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:button 
                                            wire:click="abrirModal('{{ $empresa['id'] }}')" 
                                            variant="ghost" 
                                            size="sm"
                                            icon="pencil"
                                        >
                                        </flux:button>

                                        @if($empresa['status']['valor'] === 'ATIVO')
                                            <flux:button 
                                                wire:click="inativar('{{ $empresa['id'] }}')" 
                                                variant="ghost" 
                                                size="sm"
                                                icon="pause"
                                                wire:confirm="Tem certeza que deseja inativar esta empresa?"
                                            >
                                            </flux:button>
                                        @else
                                            <flux:button 
                                                wire:click="ativar('{{ $empresa['id'] }}')" 
                                                variant="ghost" 
                                                size="sm"
                                                icon="play"
                                            >
                                            </flux:button>
                                        @endif

                                        <flux:button 
                                            wire:click="excluir('{{ $empresa['id'] }}')" 
                                            variant="ghost" 
                                            size="sm"
                                            icon="trash"
                                            wire:confirm="Tem certeza que deseja excluir esta empresa? Esta ação não pode ser desfeita."
                                        >
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $empresas->links() }}
            </div>
        @else
            <div class="py-12 text-center">
                <flux:icon.building-office class="mx-auto h-12 w-12 text-gray-400" />
                <flux:heading size="lg" class="mt-4">Nenhuma empresa encontrada</flux:heading>
                <flux:text class="mt-2">
                    @if($busca || $status || $regime_tributario)
                        Tente ajustar os filtros ou limpar a busca.
                    @else
                        Comece cadastrando sua primeira empresa.
                    @endif
                </flux:text>
                @if(!$busca && !$status && !$regime_tributario)
                    <flux:button wire:click="abrirModal" variant="primary" class="mt-4" icon="plus">
                        Cadastrar Primeira Empresa
                    </flux:button>
                @endif
            </div>
        @endif
    </flux:card>

    {{-- Modal de Formulário --}}
    @if($showModal)
        <flux:modal wire:model="showModal" class="max-w-2xl">
            <livewire:empresas.form :empresaId="$empresaSelecionada" :key="$empresaSelecionada ?? 'new'" />
        </flux:modal>
    @endif
</div>
