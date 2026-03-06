<?php

namespace App\Events;

use App\Models\ChatRoom;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ChatRoom $chatRoom;

    public function __construct(ChatRoom $chatRoom)
    {
        $this->chatRoom = $chatRoom;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chatroom.' . $this->chatRoom->nidchatroom),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'chatRoomId' => $this->chatRoom->nidchatroom,
            'status'     => $this->chatRoom->cstatus,
            'updated_at' => now()->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.status.updated';
    }
}
