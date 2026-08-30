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

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Message $message)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('message_sent'),
            new PrivateChannel('chat.' . $this->message->chat_id)
        ];
    }

    public function broadcastAs(): string
    {
        return 'new-message';
    }


    public function broadcastWith(): array
    {
//        $this->chat->load('lastMessage');
        $chat = Chat::withCount([
            'unreadMessages' => function ($q) {
                $q->where('sender_type', 'user');
            }
        ])->find($this->message->chat_id);


        return [
            'data' => $this->message,
            'unread' => $chat->unread_messages_count,
        ];
    }
}
