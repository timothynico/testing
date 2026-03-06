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
    public bool $shouldDisplayAttachmentPreview = true;
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

    /**
     * Called by the JS send-queue, one item at a time.
     *
     * $tempId   – client-side UUID to match the optimistic bubble
     * $text     – message text passed directly from JS (avoids Livewire state race condition)
     *
     * Attachment-only messages still use the Livewire file upload state; the JS queue
     * sends text as an empty string in that case.
     */
    public function sendMessage(string $tempId = '', string $text = '')
    {
        if (!$this->chatRoomId) {
            $this->queueFail($tempId);
            return;
        }

        try {
            // Validate attachment if present
            if ($this->attachment) {
                $this->validate(['attachment' => 'image|max:10240']);
            }

            $text = trim($text);

            if (strlen($text) > 5000) {
                $this->queueFail($tempId);
                return;
            }

            // Nothing to send
            if ($text === '' && !$this->attachment) {
                $this->queueFail($tempId);
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
                'nidchatroom'      => $this->chatRoomId,
                'niduser'          => Auth::id(),
                'ctext'            => $text ?: '',
                'cattachment_path' => $path,
            ]);

            $message->loadMissing('sender');
            $messagePayload = $this->formatMessage($message);

            broadcast(new ChatMessageSent($message))->toOthers();

            $this->dispatch('chat-message-appended', message: $messagePayload);
            $this->reset(['attachment']);
            $this->shouldDisplayAttachmentPreview = true;
            $this->markAsRead();

            // Signal JS queue: this item is done → remove optimistic bubble → process next
            $this->dispatch('message-confirmed',
                tempId: $tempId,
                messageId: $message->nidmessage,
                attachmentPath: $message->cattachment_path ?? null,
            );

        } catch (\Throwable $e) {
            $this->queueFail($tempId);
            throw $e;
        }
    }

    private function queueFail(string $tempId): void
    {
        if ($tempId !== '') {
            $this->dispatch('message-failed', tempId: $tempId);
        }
    }

    public function messageReceived($event)
    {
        $this->messages[] = [
            'nidmessage' => $event['nidmessage'] ?? null,
            'ctext' => $event['ctext'],
            'cattachment_path' => $event['cattachment_path'],
            'created_at' => $event['created_at'],
            'niduser' => $event['niduser'],
            'user' => [
                'name' => $event['cusername']
            ]
        ];
    }

    public function appendLatestMessage(): void
    {
        if (!$this->chatRoomId) {
            return;
        }

        $message = Message::with('sender')
            ->where('nidchatroom', $this->chatRoomId)
            ->latest('nidmessage')
            ->first();

        if (!$message) {
            return;
        }

        $this->dispatch('chat-message-appended', message: $this->formatMessage($message));
        $this->markAsRead();
    }

    private function formatMessage(Message $message): array
    {
        return [
            'nidmessage' => $message->nidmessage,
            'ctext' => $message->ctext,
            'cattachment_path' => $message->cattachment_path,
            'created_at' => $message->created_at,
            'created_at_iso' => optional($message->created_at)->toISOString(),
            'niduser' => $message->niduser,
            'user' => [
                'name' => $message->sender->name ?? 'Unknown',
            ],
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
        if (!$this->chatRoomId) return;

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
                    $q->where('creference', 'ilike', '%' . $this->search . '%')
                    ->orWhereHas('applicant', function ($q2) {
                        $q2->where('name', 'ilike', '%' . $this->search . '%')
                            ->orWhereHas('customer', function ($q3) {
                                $q3->where('cnmcust', 'ilike', '%' . $this->search . '%');
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
                    'auto_close_at' => $room->auto_close_at,
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

    public function removeAttachment()
    {
        $this->reset('attachment');
        $this->shouldDisplayAttachmentPreview = true;
        $this->dispatch('reset-file-input');
    }

    public function clearAttachmentAfterEnqueue()
    {
        $this->reset('attachment');
        $this->shouldDisplayAttachmentPreview = false;
        $this->dispatch('reset-file-input');
    }

    public function showAttachmentPreview()
    {
        $this->shouldDisplayAttachmentPreview = true;
    }

        public function backToChatRoomList()
    {
        $this->chatRoomId = null;
        $this->chatRoom = null;
        $this->messages = [];
        $this->firstUnreadMessageId = null;
    }
}
