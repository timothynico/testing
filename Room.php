<?php

namespace App\Livewire\Chat;

use App\Events\ChatMessageSent;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\ChatRoomDetail;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Room extends Component
{   
    use WithFileUploads;

    public $chatRoomId = null;
    public $chatRoom = null;
    public $messages = [];
    public $newMessage = '';
    public $search = '';
    public $filterMenu = '';
    public $filterStatus = '';
    public $pendingStatus = null;
    public $statusReason = '';
    public $idClose = null;
    public $attachment;
    public $firstUnreadMessageId = null;

    protected function canManageStatus(?ChatRoom $chatRoom): bool
    {
        if (!$chatRoom || !$chatRoom->applicant) {
            return false;
        }

        return (int) Auth::id() === (int) $chatRoom->applicant->id
            || Auth::user()?->role === 'admin';
    }

    public function mount()
    {
        //
    }

    public function selectChatRoom($id)
    {
        $this->chatRoomId = $id;
        $this->refreshChatState();
        $this->dispatch('chatRoomSelected', chatRoomId: $id);
    }

    public function sendMessage()
    {
        if (!$this->chatRoomId) {
            return;
        }

        $this->validate([
            'newMessage' => 'nullable|string|max:5000',
            'attachment' => 'nullable|image|max:2048' // max 2MB
        ]);

        // Return if both emptys
        if (!$this->newMessage && !$this->attachment) {
            return;
        }

        $path = null;

        if ($this->attachment) {
            $path = $this->attachment->store('chat-attachments', 'public');
        }

        $message = Message::create([
            'nidchatroom' => $this->chatRoomId,
            'niduser' => Auth::id(),
            'ctext' => $this->newMessage,
            'cattachment_path' => $path,
        ]);

        broadcast(new ChatMessageSent($message))->toOthers();

        $this->refreshChatState();

        $this->reset(['newMessage', 'attachment']);
    }

    public function messageReceived($event)
    {
        $this->messages[] = [
            'ctext' => $event['ctext'],
            'cattachment_path' => $event['cattachment_path'],
            'created_at' => $event['created_at'],
            'niduser' => $event['niduser'],
            'user' => [
                'name' => $event['cusername']
            ]
        ];
    }

    public function refreshChatState()
    {
        if (!$this->chatRoomId) {
            return;
        }

        $chatRoom = ChatRoom::with('applicant.customer')->find($this->chatRoomId);

        if (!$chatRoom) {
            $this->chatRoomId = null;
            $this->chatRoom = null;
            $this->messages = [];
            $this->firstUnreadMessageId = null;

            return;
        }

        $this->chatRoom = $chatRoom;

        $lastReadMessageId = ChatRoomDetail::where('nidchatroom', $this->chatRoomId)
            ->where('niduser', Auth::id())
            ->value('nidlastreadmessage');

        $this->firstUnreadMessageId = Message::query()
            ->where('nidchatroom', $this->chatRoomId)
            ->when($lastReadMessageId, function ($query) use ($lastReadMessageId) {
                $query->where('nidmessage', '>', $lastReadMessageId);
            })
            ->orderBy('nidmessage')
            ->value('nidmessage');

        $this->messages = Message::with('sender')
            ->where('nidchatroom', $this->chatRoomId)
            ->orderBy('created_at')
            ->get()
            ->map(function ($msg) {
                return [
                    'nidmessage' => $msg->nidmessage,
                    'ctext' => $msg->ctext,
                    'cattachment_path' => $msg->cattachment_path,
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
        $chatRoomList = ChatRoom::query()
            ->whereHas('members', function ($query) {
                $query->where('niduser', Auth::id());
            })

            // Searchbar query filter
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('creference', 'like', '%' . $this->search . '%')
                    ->orWhereHas('applicant', function ($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%')
                            ->orWhereHas('customer', function ($q3) {
                                $q3->where('cnmcust', 'like', '%' . $this->search . '%');
                            });
                    });
                });
            })

            // Menu query filter
            ->when($this->filterMenu, function ($query) {
                $query->where('ctype', $this->filterMenu);
            })

            // Status query filter
            ->when($this->filterStatus, function ($query) {
                $query->where('cstatus', $this->filterStatus);
            })

            ->withMax('messages', 'created_at')

            ->with(['applicant.customer', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])

            ->with(['members'])

            ->orderByDesc('messages_max_created_at')
            ->get()

            ->map(function ($room) {
                return [
                    'nidchatroom' => $room->nidchatroom,
                    'creference' => $room->creference,
                    'ctype' => $room->ctype,
                    'cstatus' => $room->cstatus,
                    'cissue' => $room->cissue,
                    'cdescription' => $room->cdescription,
                    'applicant' => $room->applicant,
                    'closedBy' => $room->closedBy,
                    'creason' => $room->creason,
                    'customer' => $room->applicant->customer,
                    'last_message_at' => $room->messages->first()?->created_at,
                    'unread_count' => $room->unreadCountFor(Auth::id()),
                ];
            });

        return view('livewire.chat.room', [
            'chatRoomList' => $chatRoomList,
        ])->layout('layouts.app');
    }

    public function updateStatus($status = null)
    {
        if (!$this->chatRoomId) {
            return;
        }

        if ($status !== null) {
            if (!in_array($status, ['resolved', 'closed'], true)) {
                return;
            }

            $this->chatRoom = ChatRoom::findOrFail($this->chatRoomId);

            if (!$this->canManageStatus($this->chatRoom)) {
                return;
            }

            if ($status === 'resolved' && in_array($this->chatRoom->cstatus, ['resolved', 'closed'], true)) {
                return;
            }

            if ($status === 'closed' && $this->chatRoom->cstatus === 'closed') {
                return;
            }

            $this->pendingStatus = $status;
            $this->statusReason = '';

            $this->dispatch('open-status-modal');

            return;
        }

        $this->validate([
            'pendingStatus' => 'required|in:resolved,closed',
            'statusReason' => 'required|string|max:1000',
        ]);

        $chatRoom = ChatRoom::findOrFail($this->chatRoomId);

        if (!$this->canManageStatus($chatRoom)) {
            return;
        }

        $chatRoom->update([
            'cstatus' => $this->pendingStatus,
            'nidclose' => Auth::id(),
            'creason' => $this->statusReason,
        ]);

        $this->refreshChatState();
        $this->pendingStatus = null;
        $this->statusReason = '';

        $this->dispatch('close-status-modal');
    }
}
