<li role="none">
    @if(isset($item['children']))
        <!-- Menu com submenu -->
        <div x-data="{ 
            expanded: @entangle('expanded'),
            showTooltip: false 
        }">
            <button wire:click="toggle"
                    @mouseenter="if ($wire.collapsed) showTooltip = true"
                    @mouseleave="showTooltip = false"
                    class="flex w-full items-center justify-between rounded-lg p-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                    :class="{ 'justify-center': $wire.collapsed }"
                    :aria-expanded="expanded"
                    :aria-controls="'submenu-' + '{{ $item['id'] }}'"
                    :aria-label="$wire.collapsed ? '{{ $item['label'] }}' : 'Expandir menu {{ $item['label'] }}'"
                    role="menuitem">
                <div class="flex items-center gap-3">
                    @if($item['icon'] === 'home')
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    @elseif($item['icon'] === 'folder')
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    @elseif($item['icon'] === 'shopping-cart')
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 11-4 0v-6m4 0V9a2 2 0 10-4 0v4.01"/>
                        </svg>
                    @elseif($item['icon'] === 'currency-dollar')
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    @elseif($item['icon'] === 'document-text')
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    @elseif($item['icon'] === 'chart-bar')
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    @endif
                    
                    <span x-show="!$wire.collapsed" 
                          x-transition:enter="transition-opacity duration-300"
                          x-transition:enter-start="opacity-0"
                          x-transition:enter-end="opacity-100"
                          x-transition:leave="transition-opacity duration-200"
                          x-transition:leave-start="opacity-100"
                          x-transition:leave-end="opacity-0"
                          class="text-sm font-medium">
                        {{ $item['label'] }}
                    </span>
                </div>
                
                <!-- Chevron Icon -->
                <svg x-show="!$wire.collapsed && !expanded" 
                     x-transition
                     class="h-4 w-4 transition-transform" 
                     fill="none" 
                     stroke="currentColor" 
                     viewBox="0 0 24 24"
                     aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <svg x-show="!$wire.collapsed && expanded" 
                     x-transition
                     class="h-4 w-4 transition-transform" 
                     fill="none" 
                     stroke="currentColor" 
                     viewBox="0 0 24 24"
                     aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            <!-- Submenu -->
            <ul x-show="!$wire.collapsed && expanded" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                id="submenu-{{ $item['id'] }}"
                class="ml-8 mt-1 space-y-1"
                role="menu"
                aria-label="Submenu {{ $item['label'] }}">
                @foreach($item['children'] as $child)
                    <li role="none">
                        <a href="{{ $this->itemUrl($child) }}"
                           class="block rounded-lg p-2 text-sm text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors"
                           :class="{ 'bg-blue-50 text-blue-600 dark:bg-blue-900 dark:text-blue-300': {{ $child['active'] ? 'true' : 'false' }} }"
                           role="menuitem">
                            {{ $child['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
            
            <!-- Tooltip para sidebar colapsada -->
            <div x-show="$wire.collapsed && showTooltip" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute left-full top-0 z-50 ml-2 whitespace-nowrap rounded-lg bg-gray-900 px-3 py-2 text-sm text-white shadow-lg">
                {{ $item['label'] }}
                <div class="absolute left-0 top-1/2 -ml-1 -translate-y-1/2">
                    <div class="h-2 w-2 rotate-45 bg-gray-900"></div>
                </div>
            </div>
        </div>
    @else
        <!-- Menu simples (sem submenu) -->
        <div x-data="{ showTooltip: false }" class="relative">
            <a href="{{ $this->itemUrl($item) }}"
               @mouseenter="if ($wire.collapsed) showTooltip = true"
               @mouseleave="showTooltip = false"
               class="flex items-center gap-3 rounded-lg p-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
               :class="{ 
                   'justify-center': $wire.collapsed,
                   'bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700': {{ $item['active'] ? 'true' : 'false' }}
               }"
               role="menuitem"
               :aria-label="$wire.collapsed ? '{{ $item['label'] }}' : null">
                
                @if($item['icon'] === 'home')
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                @elseif($item['icon'] === 'chart-bar')
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                @endif
                
                <span x-show="!$wire.collapsed" 
                      x-transition:enter="transition-opacity duration-300"
                      x-transition:enter-start="opacity-0"
                      x-transition:enter-end="opacity-100"
                      x-transition:leave="transition-opacity duration-200"
                      x-transition:leave-start="opacity-100"
                      x-transition:leave-end="opacity-0"
                      class="text-sm font-medium">
                    {{ $item['label'] }}
                </span>
            </a>
            
            <!-- Tooltip para sidebar colapsada -->
            <div x-show="$wire.collapsed && showTooltip" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute left-full top-0 z-50 ml-2 whitespace-nowrap rounded-lg bg-gray-900 px-3 py-2 text-sm text-white shadow-lg">
                {{ $item['label'] }}
                <div class="absolute left-0 top-1/2 -ml-1 -translate-y-1/2">
                    <div class="h-2 w-2 rotate-45 bg-gray-900"></div>
                </div>
            </div>
        </div>
    @endif
</li>
