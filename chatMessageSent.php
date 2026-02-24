<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load('user');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chatroom.' . $this->message->nidchatroom),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'nidmessage' => $this->message->nidmessage,
            'nidchatroom' => $this->message->nidchatroom,
            'niduser' => $this->message->niduser,
            'cusername' => $this->message->user->name ?? 'Unknown',
            'ctext' => $this->message->ctext,
            'created_at' => $this->message->created_at->toISOString(),
        ];
    }
}
