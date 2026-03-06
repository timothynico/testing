<?php
// app/Events/ChatRoomCreated.php

namespace App\Events;

use App\Models\ChatRoom;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatRoomCreated implements ShouldBroadcastNow
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


    public function broadcastWith(): array
    {
        $chatRoom = $this->chatRoom->loadMissing('applicant.customer');

        return [
            'chatroom' => [
                'nidchatroom' => $chatRoom->nidchatroom,
                'creference' => $chatRoom->creference,
                'ctype' => $chatRoom->ctype,
                'cstatus' => $chatRoom->cstatus,
                'cissue' => $chatRoom->cissue,
                'applicant_name' => $chatRoom->applicant->name ?? 'Unknown',
                'customer_name' => $chatRoom->applicant?->customer?->cnmcust ?? 'Customer',
                'unread_count' => 0,
            ],
        ];
    }

    public function broadcastAs(): string
    {
        return 'chatroom.created';
    }
}
