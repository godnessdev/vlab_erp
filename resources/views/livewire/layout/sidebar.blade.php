<aside x-data="{ 
    collapsed: @entangle('collapsed'),
    expandedMenus: @entangle('expandedMenus'),
    mobileOpen: false
}"
    x-init="
        collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        $watch('collapsed', val => localStorage.setItem('sidebarCollapsed', val));
    "
    @toggle-sidebar.window="mobileOpen = !mobileOpen"
    :class="collapsed ? 'w-16' : 'w-64'"
    class="flex flex-col border-r border-gray-200 bg-white transition-all duration-300 dark:border-gray-700 dark:bg-gray-800 md:relative md:translate-x-0"
    :class="{
        'fixed inset-y-0 left-0 z-50 translate-x-0': mobileOpen,
        'fixed inset-y-0 left-0 z-50 -translate-x-full': !mobileOpen
    }"
    role="navigation"
    aria-label="Menu principal">
    
    <!-- Mobile Overlay -->
    <div x-show="mobileOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileOpen = false"
         class="fixed inset-0 bg-gray-600 bg-opacity-75 md:hidden"></div>
    
    <!-- Logo -->
    <div class="flex h-16 items-center justify-center border-b border-gray-200 dark:border-gray-700">
        <a href="{{ route('dashboard') }}" class="flex items-center">
            <div :class="collapsed ? 'h-8 w-8' : 'h-10 w-10'"
                 class="flex items-center justify-center rounded-lg bg-blue-600 text-white transition-all duration-300">
                <span :class="collapsed ? 'text-lg' : 'text-xl'" class="font-bold">E</span>
            </div>
            <span x-show="!collapsed" 
                  x-transition:enter="transition-opacity duration-300"
                  x-transition:enter-start="opacity-0"
                  x-transition:enter-end="opacity-100"
                  x-transition:leave="transition-opacity duration-200"
                  x-transition:leave-start="opacity-100"
                  x-transition:leave-end="opacity-0"
                  class="ml-2 text-xl font-bold text-gray-900 dark:text-white">
                ERP
            </span>
        </a>
    </div>
    
    <!-- Menu Items -->
    <nav class="flex-1 overflow-y-auto p-4" role="menubar">
        <ul class="space-y-1" role="none">
            @foreach($menuItems as $item)
                <livewire:layout.sidebar-menu-item 
                    :item="$item" 
                    :collapsed="$collapsed"
                    :key="$item['id']" />
            @endforeach
        </ul>
    </nav>
    
    <!-- Collapse Toggle -->
    <div class="border-t border-gray-200 p-4 dark:border-gray-700">
        <button wire:click="toggleCollapse"
                class="flex w-full items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors"
                :aria-label="collapsed ? 'Expandir menu' : 'Recolher menu'"
                :title="collapsed ? 'Expandir menu' : 'Recolher menu'">
            <svg x-show="!collapsed" 
                 x-transition
                 class="h-5 w-5" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24"
                 aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
            <svg x-show="collapsed" 
                 x-transition
                 class="h-5 w-5" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24"
                 aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
</aside>