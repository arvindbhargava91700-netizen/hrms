<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AppNotification;

class NotificationBell extends Component
{
    public $notifications = [];
    public $unreadCount = 0;

    protected $listeners = ['refreshNotifications' => 'loadNotifications'];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = auth()->user();
        if ($user) {
            $this->notifications = AppNotification::where('user_id', $user->id)
                ->latest()
                ->take(10)
                ->get();
            $this->unreadCount = AppNotification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();
        }
    }

    public function markAsRead($notificationId)
    {
        $notification = AppNotification::where('user_id', auth()->id())->find($notificationId);
        if ($notification && !$notification->read_at) {
            $notification->update(['read_at' => now()]);
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        AppNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $this->loadNotifications();
    }

    public function deleteNotification($notificationId)
    {
        $notification = AppNotification::where('user_id', auth()->id())->find($notificationId);
        if ($notification) {
            $notification->delete();
            $this->loadNotifications();
        }
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
