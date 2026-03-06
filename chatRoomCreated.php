<?php
// app/Events/ChatRoomCreated.php

namespace App\Events;

use App\Models\ChatRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatRoomCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChatRoom $chatRoom) {}

    public function broadcastOn(): array
    {
        // Broadcast ke semua member chatroom tersebut
        return $this->chatRoom->members->map(function ($member) {
            return new PrivateChannel('user.' . $member->id);
        })->toArray();
    }

    public function broadcastAs(): string
    {
        return 'chatroom.created';
    }
}
