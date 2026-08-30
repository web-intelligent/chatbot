<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReadMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Chat $chat,
        public ?array $messageIds = null
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('read_message.' . $this->chat->id),
            new PrivateChannel('user_read_message.' . $this->chat->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'read_message';
    }

    public function broadcastWith(): array
    {
        if ($this->messageIds !== null) {

            return [
                'chat_id' => $this->chat->id,
                'message_ids' => $this->messageIds,
                'full_sync' => false,
            ];
        }

        return [
            'chat_id' => $this->chat->id,
            'messages' => Message::where(
                'chat_id',
                $this->chat->id
            )->get(['id', 'is_read']),
            'full_sync' => true,
        ];
    }
}
