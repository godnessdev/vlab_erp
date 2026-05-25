<div x-data="{ open: @entangle('open') }" 
     @click.away="open = false"
     class="relative">
    
    <!-- Notification Bell -->
    <button @click="open = !open"
            class="relative flex min-h-11 min-w-11 items-center justify-center rounded-lg p-2 text-gray-600 transition-colors hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700"
            :aria-expanded="open"
            aria-haspopup="true"
            aria-label="Notificações">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        
        @if($this->unreadCount > 0)
            <span class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
            </span>
        @endif
    </button>
    
    <!-- Dropdown -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 top-full z-50 mt-2 w-80 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
         role="menu"
         aria-label="Lista de notificações">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                Notificações
            </h3>
            @if($this->unreadCount > 0)
                <button wire:click="markAllAsRead"
                        class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                    Marcar todas como lidas
                </button>
            @endif
        </div>
        
        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
            @forelse($this->notifications as $notification)
                <div class="border-b border-gray-100 px-4 py-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700 transition-colors last:border-0"
                     :class="{ 'bg-blue-50 dark:bg-blue-900/20': {{ !$notification['read'] ? 'true' : 'false' }} }">
                    <div class="flex items-start gap-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mt-0.5">
                            @if($notification['icon'] === 'information-circle')
                                <div class="rounded-full bg-blue-100 p-1 dark:bg-blue-900">
                                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @elseif($notification['icon'] === 'check-circle')
                                <div class="rounded-full bg-green-100 p-1 dark:bg-green-900">
                                    <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @else
                                <div class="rounded-full bg-gray-100 p-1 dark:bg-gray-700">
                                    <svg class="h-4 w-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <button wire:click="markAsRead('{{ $notification['id'] }}')"
                                    class="block w-full text-left">
                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $notification['title'] }}
                                </div>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $notification['message'] }}
                                </div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                    {{ $notification['created_at']->diffForHumans() }}
                                </div>
                            </button>
                        </div>
                        
                        <!-- Unread Indicator -->
                        @if(!$notification['read'])
                            <div class="flex-shrink-0 mt-2">
                                <div class="h-2 w-2 rounded-full bg-blue-600"></div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13.5v-8A2.5 2.5 0 0017.5 3h-11A2.5 2.5 0 004 5.5v8a2.5 2.5 0 002.5 2.5H8l4 4 4-4h1.5a2.5 2.5 0 002.5-2.5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h8m-8 4h6"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Nenhuma notificação
                    </p>
                </div>
            @endforelse
        </div>
        
        <!-- Footer -->
        @if($this->notifications->isNotEmpty())
            <div class="border-t border-gray-200 p-2 dark:border-gray-700">
                <a href="#"
                   class="block rounded-lg px-4 py-2 text-center text-sm text-blue-600 hover:bg-gray-100 dark:text-blue-400 dark:hover:bg-gray-700 transition-colors">
                    Ver todas as notificações
                </a>
            </div>
        @endif
    </div>
</div>
