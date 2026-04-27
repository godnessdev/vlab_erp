<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
      x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <livewire:layout.sidebar />
        
        <!-- Main Content Area -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Topbar -->
            <livewire:layout.topbar />
            
            <!-- Page Content -->
            <main id="main-content" class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8" tabindex="-1">
                {{ $slot }}
            </main>
        </div>
    </div>
    
    <!-- Toast Notifications -->
    <div x-data="toastManager()" 
         @notify.window="show($event.detail)"
         class="fixed bottom-4 right-4 z-50 space-y-2">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-full"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform translate-x-full"
                 class="rounded-lg px-4 py-3 shadow-lg max-w-sm"
                 :class="{
                     'bg-green-500 text-white': toast.type === 'success',
                     'bg-red-500 text-white': toast.type === 'error',
                     'bg-blue-500 text-white': toast.type === 'info',
                     'bg-yellow-500 text-white': toast.type === 'warning'
                 }">
                <div class="flex items-center gap-2">
                    <span x-text="toast.message"></span>
                    <button @click="remove(toast.id)" class="ml-2 hover:opacity-75">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>
    
    <!-- Skip Link for Accessibility -->
    <a href="#main-content" 
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:bg-blue-600 focus:text-white focus:px-4 focus:py-2 focus:rounded">
        Pular para conteúdo principal
    </a>
    
    @livewireScripts
    <script>
        function toastManager() {
            return {
                toasts: [],
                nextId: 1,
                show(detail) {
                    const id = this.nextId++;
                    const toast = { id, ...detail, visible: true };
                    this.toasts.push(toast);
                    setTimeout(() => this.remove(id), detail.duration || 3000);
                },
                remove(id) {
                    const index = this.toasts.findIndex(t => t.id === id);
                    if (index > -1) {
                        this.toasts[index].visible = false;
                        setTimeout(() => {
                            this.toasts.splice(index, 1);
                        }, 300);
                    }
                }
            }
        }
        
        // Detect reduced motion preference
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.style.setProperty('--transition-duration', '0.01ms');
        }
    </script>
</body>
</html>