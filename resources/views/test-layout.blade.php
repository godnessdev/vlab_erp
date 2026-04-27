<x-layouts.app title="Test Layout">
    <x-slot name="sidebar">
        <!-- Temporary sidebar content for testing -->
        <ul class="space-y-1">
            <li>
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 rounded-lg p-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 bg-blue-600 text-white hover:bg-blue-700">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-sm font-medium">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#" 
                   class="flex items-center gap-3 rounded-lg p-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    <span class="text-sm font-medium">Cadastros</span>
                </a>
            </li>
        </ul>
    </x-slot>
    
    <x-slot name="topbar">
        <!-- Temporary topbar content for testing -->
        <div class="flex items-center gap-4">
            <!-- Mobile Menu Toggle -->
            <button @click="$dispatch('toggle-sidebar')"
                    class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 md:hidden">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            
            <!-- Company Name -->
            <div class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="hidden md:inline">Empresa Teste</span>
            </div>
            
            <!-- Breadcrumbs -->
            <nav class="hidden md:flex items-center gap-2 text-sm">
                <span class="font-medium text-gray-900 dark:text-gray-100">Dashboard</span>
            </nav>
        </div>
        
        <!-- Right: User Controls -->
        <div class="flex items-center gap-2">
            <!-- Dark Mode Toggle -->
            <button @click="darkMode = !darkMode"
                    class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors">
                <svg x-show="darkMode" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <svg x-show="!darkMode" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>
            
            <!-- User Profile -->
            <div class="flex items-center gap-2 rounded-lg p-2 text-gray-700 dark:text-gray-300">
                <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-medium">
                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                </div>
                <span class="hidden md:inline text-sm font-medium">{{ auth()->user()->name ?? 'Usuário' }}</span>
            </div>
        </div>
    </x-slot>
    
    <!-- Main Content -->
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                Test Layout
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Testing the base layout structure with sidebar, topbar, and content area.
            </p>
        </div>
        
        <!-- Test Cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-4">
                    <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Faturamento</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">R$ 0,00</p>
                    </div>
                </div>
            </div>
            
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-4">
                    <div class="rounded-full bg-green-100 p-3 dark:bg-green-900">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Vendas</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">0</p>
                    </div>
                </div>
            </div>
            
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-4">
                    <div class="rounded-full bg-yellow-100 p-3 dark:bg-yellow-900">
                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">A Receber</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">R$ 0,00</p>
                    </div>
                </div>
            </div>
            
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-4">
                    <div class="rounded-full bg-red-100 p-3 dark:bg-red-900">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">A Pagar</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">R$ 0,00</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Test Toast Button -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Test Toast Notifications</h2>
            <div class="flex flex-wrap gap-2">
                <button onclick="toast('Operação realizada com sucesso!', 'success')"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                    Success Toast
                </button>
                <button onclick="toast('Ocorreu um erro ao processar a solicitação.', 'error')"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                    Error Toast
                </button>
                <button onclick="toast('Esta é uma informação importante.', 'info')"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                    Info Toast
                </button>
                <button onclick="toast('Atenção: verifique os dados antes de continuar.', 'warning')"
                        class="rounded-lg bg-yellow-600 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-700 transition-colors">
                    Warning Toast
                </button>
            </div>
        </div>
    </div>
</x-layouts.app>
