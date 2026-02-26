<?php

namespace App\Livewire\Chat;

use App\Events\ChatMessageSent;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\ChatRoomDetail;
use App\Services\ImageUploadService;
use Illuminate\Support\Carbon;
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
    public $groupedMessages = [];

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
            'attachment' => 'nullable|image|max:10240' // max 10MB
        ]);

        // Return if both emptys
        if (!$this->newMessage && !$this->attachment) {
            return;
        }

        $path = null;

        if ($this->attachment) {
            $path = ImageUploadService::compressAndStore(
                $this->attachment,
                'chat-attachments'
            );
        }

        $message = Message::create([
            'nidchatroom' => $this->chatRoomId,
            'niduser' => Auth::id(),
            'ctext' => $this->newMessage,
            'cattachment_path' => $path,
        ])->load('sender');

        broadcast(new ChatMessageSent($message))->toOthers();

        $this->messages[] = $this->formatMessage($message);
        $this->regroupMessages();
        $this->markAsRead();

        $this->reset(['newMessage', 'attachment']);
    }

    public function messageReceived($event)
    {
        if ((int) ($event['nidchatroom'] ?? 0) !== (int) $this->chatRoomId) {
            return;
        }

        $incomingId = (int) ($event['nidmessage'] ?? 0);
        if ($incomingId > 0 && collect($this->messages)->contains(fn ($message) => (int) ($message['nidmessage'] ?? 0) === $incomingId)) {
            return;
        }

        $this->messages[] = [
            'nidmessage' => $event['nidmessage'] ?? null,
            'ctext' => $event['ctext'] ?? '',
            'cattachment_path' => $event['cattachment_path'] ?? null,
            'created_at' => $event['created_at'] ?? now()->toISOString(),
            'niduser' => $event['niduser'] ?? null,
            'user' => [
                'name' => $event['cusername'] ?? 'Unknown',
            ],
        ];

        $this->regroupMessages();
    }

    public function refreshChatState()
    {
        if (!$this->chatRoomId) {
            return;
        }

        $chatRoom = ChatRoom::with(['applicant.customer', 'members.customer', 'closedBy'])->find($this->chatRoomId);

        if (!$chatRoom) {
            $this->chatRoomId = null;
            $this->chatRoom = null;
            $this->messages = [];
            $this->groupedMessages = [];
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

        $this->messages = Message::with('sender:id,name')
            ->where('nidchatroom', $this->chatRoomId)
            ->select(['nidmessage', 'nidchatroom', 'niduser', 'ctext', 'cattachment_path', 'created_at'])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($msg) => $this->formatMessage($msg))
            ->toArray();

        $this->regroupMessages();

        $this->markAsRead();
    }

    public function markAsRead()
    {
        if (!$this->chatRoomId) return;

        $lastMessageId = Message::where('nidchatroom', $this->chatRoomId)
            ->latest('nidmessage')
            ->value('nidmessage');

        ChatRoomDetail::updateOrCreate(
            [
                'nidchatroom' => $this->chatRoomId,
                'niduser' => Auth::id(),
            ],
            [
                'nidlastreadmessage' => $lastMessageId,
            ]
        );
    }

    public function render()
    {
        $authId = (int) Auth::id();

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
                if ($this->filterStatus === 'closed') {
                    $query->where(function ($q) {
                        $q->where('cstatus', 'closed')
                        ->orWhereRaw("
                            (
                                SELECT MAX(created_at)
                                FROM tmessages
                                WHERE tmessages.nidchatroom = tchatrooms.nidchatroom
                            ) <= ?
                        ", [Carbon::now()->subDays(2)]);
                    });
                } else {
                    $query->where('cstatus', $this->filterStatus);
                }
            })

            ->withMax('messages', 'created_at')

            ->select('tchatrooms.*')
            ->selectRaw(
                '(
                    SELECT COUNT(*)
                    FROM tmessages
                    WHERE tmessages.nidchatroom = tchatrooms.nidchatroom
                        AND tmessages.niduser != ?
                        AND tmessages.nidmessage > COALESCE((
                            SELECT tchatroomdtl.nidlastreadmessage
                            FROM tchatroomdtl
                            WHERE tchatroomdtl.nidchatroom = tchatrooms.nidchatroom
                                AND tchatroomdtl.niduser = ?
                            LIMIT 1
                        ), 0)
                ) AS unread_count',
                [$authId, $authId]
            )

            ->with(['applicant.customer', 'closedBy'])

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
                    'last_message_at' => $room->messages_max_created_at,
                    'unread_count' => (int) ($room->unread_count ?? 0),
                ];
            });

            $this->dispatch('allChatroomIds', [
                'ids' => $chatRoomList->pluck('nidchatroom')->toArray()
            ]);

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

        broadcast(new \App\Events\ChatStatusUpdated($chatRoom))->toOthers();

        $this->refreshChatState();
        $this->pendingStatus = null;
        $this->statusReason = '';

        $this->dispatch('close-status-modal');
    }

    public function refreshSidebar()
    {
        if ($this->chatRoomId) {
            $this->refreshChatState();
        }
        // Livewire 3 otomatis panggil render() setelah method ini,
        // sehingga unread_count sidebar selalu terupdate
    }

    protected function formatMessage(Message $message): array
    {
        return [
            'nidmessage' => $message->nidmessage,
            'ctext' => $message->ctext,
            'cattachment_path' => $message->cattachment_path,
            'created_at' => $message->created_at,
            'niduser' => $message->niduser,
            'user' => [
                'name' => $message->sender->name ?? $message->user->name ?? 'Unknown',
            ],
        ];
    }

    protected function regroupMessages(): void
    {
        $this->groupedMessages = collect($this->messages)
            ->groupBy(function ($message) {
                return Carbon::parse($message['created_at'])->toDateString();
            })
            ->toArray();
    }
}
