<?php

namespace MyListerHub\Core\Concerns\Notifications;

use Illuminate\Notifications\Messages\BroadcastMessage;

trait BroadcastableNotification
{
    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => $this->toArray($notifiable),
            'read_at' => null,
            'created_at' => now()->toIso8601ZuluString(),
        ]);
    }
}
