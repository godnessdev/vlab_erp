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
        return collect();
    }

    #[Computed]
    public function unreadCount(): int
    {
        return 0;
    }

    public function markAsRead(string $notificationId): void
    {
        $this->dispatch('notification-read');
    }

    public function markAllAsRead(): void
    {
        $this->dispatch('notifications-read');
    }

    public function render()
    {
        return view('livewire.notifications.notification-dropdown');
    }
}
