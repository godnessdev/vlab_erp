<?php

namespace App\Livewire\Notifications;

use Livewire\Attributes\Computed;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public bool $open = false;

    #[Computed]
    public function notifications()
    {
        // TODO: Implementar quando sistema de notificações estiver pronto
        // Por enquanto, retornar notificações de exemplo
        return collect([
            [
                'id' => '1',
                'type' => 'info',
                'title' => 'Bem-vindo ao ERP',
                'message' => 'Sistema de interface profissional implementado com sucesso.',
                'read' => false,
                'created_at' => now()->subMinutes(5),
                'icon' => 'information-circle',
            ],
            [
                'id' => '2',
                'type' => 'success',
                'title' => 'Dashboard atualizado',
                'message' => 'Nova interface do dashboard está disponível.',
                'read' => false,
                'created_at' => now()->subHours(2),
                'icon' => 'check-circle',
            ],
        ]);

        // Futuro código:
        // return auth()->user()
        //     ->notifications()
        //     ->latest()
        //     ->take(10)
        //     ->get();
    }

    #[Computed]
    public function unreadCount()
    {
        return $this->notifications->where('read', false)->count();

        // Futuro código:
        // return auth()->user()
        //     ->unreadNotifications()
        //     ->count();
    }

    public function markAsRead($notificationId)
    {
        // TODO: Implementar quando sistema de notificações estiver pronto
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Notificação marcada como lida.',
        ]);

        // Futuro código:
        // $notification = auth()->user()
        //     ->notifications()
        //     ->find($notificationId);
        //
        // if ($notification) {
        //     $notification->markAsRead();
        //     $this->dispatch('notification-read');
        // }
    }

    public function markAllAsRead()
    {
        // TODO: Implementar quando sistema de notificações estiver pronto
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Todas as notificações foram marcadas como lidas.',
        ]);

        // Futuro código:
        // auth()->user()->unreadNotifications->markAsRead();
        // $this->dispatch('notifications-read');
    }

    public function render()
    {
        return view('livewire.notifications.notification-dropdown');
    }
}
