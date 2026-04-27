<header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 dark:border-gray-700 dark:bg-gray-800 md:px-6">
    <!-- Left: Mobile Menu Toggle & Company Name & Breadcrumbs -->
    <div class="flex items-center gap-4">
        <!-- Mobile Menu Toggle -->
        <button @click="$dispatch('toggle-sidebar')"
                class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 md:hidden transition-colors"
                aria-label="Abrir menu de navegação">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        
        <!-- Company Selector -->
        <livewire:layout.company-selector />
        
        <!-- Breadcrumbs (hidden on mobile) -->
        <nav class="hidden md:flex items-center gap-2 text-sm" aria-label="Breadcrumb">
            @foreach($this->breadcrumbs as $index => $crumb)
                @if($index > 0)
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                @endif
                
                @if($crumb['route'])
                    <a href="{{ route($crumb['route']) }}" 
                       class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 transition-colors">
                        {{ $crumb['label'] }}
                    </a>
                @else
                    <span class="font-medium text-gray-900 dark:text-gray-100">
                        {{ $crumb['label'] }}
                    </span>
                @endif
            @endforeach
        </nav>
    </div>
    
    <!-- Right: User Controls -->
    <div class="flex items-center gap-2">
        <!-- Notifications -->
        <livewire:notifications.notification-dropdown />
        
        <!-- Dark Mode Toggle -->
        <livewire:user.dark-mode-toggle />
        
        <!-- User Profile -->
        <livewire:user.profile-dropdown />
    </div>
</header>