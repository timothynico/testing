<?php

namespace App\Livewire\Chat;

use App\Events\ChatMessageSent;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\ChatRoomDetail;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Room extends Component
{
    public $chatRoomId = null;
    public $chatRoom = null;
    public $messages = [];
    public $newMessage = '';
    public $pendingStatus = null;
    public $statusReason = '';
    public $idClose = null;

    public function mount()
    {
        //
    }

    public function selectChatRoom($id)
    {
        $this->chatRoomId = $id;

        $this->chatRoom = ChatRoom::with('applicant')
            ->findOrFail($id);

        $this->messages = Message::with('sender')
            ->where('nidchatroom', $id)
            ->orderBy('created_at')
            ->get()
            ->map(function ($msg) {
                return [
                    'ctext' => $msg->ctext,
                    'created_at' => $msg->created_at,
                    'niduser' => $msg->niduser,
                    'user' => [
                        'name' => $msg->sender->name ?? 'Unknown'
                    ]
                ];
            })
            ->toArray();

        $this->markAsRead();
    }

    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|max:5000'
        ]);

        $message = Message::create([
            'nidchatroom' => $this->chatRoomId,
            'niduser' => Auth::id(),
            'ctext' => $this->newMessage,
        ]);

        broadcast(new ChatMessageSent($message))->toOthers();

        $this->newMessage = '';

        // langsung append tanpa reload full
        $this->messages[] = [
            'ctext' => $message->ctext,
            'created_at' => $message->created_at,
            'niduser' => $message->niduser,
            'user' => [
                'name' => Auth::user()->name
            ]
        ];
    }

    public function getListeners()
    {
        if (!$this->chatRoomId) {
            return [];
        }

        return [
            "echo-private:chatroom.{$this->chatRoomId},ChatMessageSent" => 'messageReceived',
        ];
    }

    public function messageReceived($event)
    {
        $this->messages[] = [
            'ctext' => $event['ctext'],
            'created_at' => $event['created_at'],
            'niduser' => $event['niduser'],
            'user' => [
                'name' => $event['cusername']
            ]
        ];
    }

    public function markAsRead()
    {
        $lastMessageId = Message::where('nidchatroom', $this->chatRoomId)
            ->latest('nidmessage')
            ->value('nidmessage');

        ChatRoomDetail::where('nidchatroom', $this->chatRoomId)
            ->where('niduser', Auth::id())
            ->update([
                'nidlastreadmessage' => $lastMessageId
            ]);
    }

    public function render()
    {
        $chatRoomList = ChatRoom::whereHas('members', function ($query) {
                $query->where('niduser', Auth::user()->id);
            })
            ->with(['applicant', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->latest()
            ->get()
            ->map(function ($room) {
                return [
                    'nidchatroom' => $room->nidchatroom,
                    'creference' => $room->creference,
                    'ctype' => $room->ctype,
                    'cstatus' => $room->cstatus,
                    'cdescription' => $room->cdescription,
                    'applicant' => $room->applicant,
                    'last_message_at' => $room->messages->first()?->created_at,
                    'unread_count' => $room->unreadCountFor(Auth::id()),
                ];
            });

        return view('livewire.chat.room', [
            'chatRoomList' => $chatRoomList,
        ])->layout('layouts.app');
    }

    public function updateStatus($status)
    {
        if (!$this->chatRoomId) {
            return;
        }

        if (!in_array($status, ['resolved', 'closed'], true)) {
            return;
        }

        $this->chatRoom = ChatRoom::findOrFail($this->chatRoomId);

        if ($status === 'resolved' && in_array($this->chatRoom->cstatus, ['resolved', 'closed'], true)) {
            return;
        }

        if ($status === 'closed' && $this->chatRoom->cstatus === 'closed') {
            return;
        }

        $this->pendingStatus = $status;
        $this->statusReason = '';

        $this->dispatch('open-status-modal');
    }

    public function submitStatusUpdate()
    {
        $this->validate([
            'pendingStatus' => 'required|in:resolved,closed',
            'statusReason' => 'required|string|max:1000',
        ]);

        if (!$this->chatRoomId) {
            return;
        }

        $chatRoom = ChatRoom::findOrFail($this->chatRoomId);

        $chatRoom->update([
            'cstatus' => $this->pendingStatus,
            'nidclose' => Auth::id(),
            'creason' => $this->statusReason,
        ]);

        $this->chatRoom = $chatRoom->fresh('applicant');
        $this->pendingStatus = null;
        $this->statusReason = '';

        $this->dispatch('close-status-modal');
    }
}
