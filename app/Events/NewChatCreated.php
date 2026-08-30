<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewChatCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Chat $chat, public $message)
    {

    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('support-dashboard'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'new-chat';
    }

    public function broadcastWith(): array
    {
//        return [
//            'chat' => $this->chat->load(['messages' => function ($q) {
//                $q->latest()->limit(1);
//            }]),
//        ];

        $this->chat->load('lastMessage');

        return [
            'chat' => $this->chat,
            'last_message' => $this->message,
            'unread' => $this->chat->unreadMessages()->count(),
            'user' => $this->chat->user()->first(),
        ];
    }
}
