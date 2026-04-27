<div x-data="{ open: @entangle('open') }" 
     @click.away="open = false"
     class="relative">
    
    @if($this->currentCompany)
        @if($this->hasMultipleCompanies)
            <!-- Dropdown Trigger -->
            <button @click="open = !open"
                    class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors"
                    :aria-expanded="open"
                    aria-haspopup="true"
                    aria-label="Selecionar empresa">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="hidden md:inline truncate max-w-32">{{ $this->currentCompany->nome_fantasia }}</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            <!-- Dropdown Menu -->
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute left-0 top-full z-50 mt-2 w-64 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                 role="menu"
                 aria-label="Lista de empresas">
                <div class="p-2">
                    <div class="mb-2 px-3 py-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                        Selecionar Empresa
                    </div>
                    <ul class="space-y-1" role="none">
                        @foreach($this->availableCompanies as $company)
                            <li role="none">
                                <button wire:click="selectCompany('{{ $company->id }}')"
                                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        :class="{ 'bg-blue-50 text-blue-600 dark:bg-blue-900 dark:text-blue-300': {{ $company->id === $this->currentCompany->id ? 'true' : 'false' }} }"
                                        role="menuitem">
                                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium truncate">{{ $company->nome_fantasia }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {{ $company->razao_social }}
                                        </div>
                                    </div>
                                    @if($company->id === $this->currentCompany->id)
                                        <svg class="h-5 w-5 flex-shrink-0 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @else
            <!-- Single Company Display -->
            <div class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="hidden md:inline truncate max-w-32">{{ $this->currentCompany->nome_fantasia }}</span>
            </div>
        @endif
    @else
        <!-- No Company -->
        <div class="flex items-center gap-2 text-sm font-medium text-red-600 dark:text-red-400">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <span class="hidden md:inline">Nenhuma empresa</span>
        </div>
    @endif
</div>