<div>
<div class="card border shadow-sm" style="height: calc(100vh - 200px); min-height: 600px;">
    <div class="card-body p-0 d-flex" style="height: 100%;">
        {{-- Left Sidebar - Feedback List --}}
        <div class="feedback-sidebar border-end" id="feedbackSidebar">
            {{-- Search & Filter Header --}}
            <div class="sidebar-header border-bottom p-3">
                <div class="input-group input-group-sm mb-2">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0"
                        placeholder="{{ __('Search feedback...') }}" id="searchFeedback"
                        wire:model.live="search">
                </div>

                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="filterMenu" wire:model.live="filterMenu">
                        <option value="">{{ __('All Menus') }}</option>
                        <option value="delivery">{{ __('Delivery') }}</option>
                        <option value="invoice">{{ __('Invoice') }}</option>
                        <option value="agreement">{{ __('Agreement') }}</option>
                        <option value="report">{{ __('Report') }}</option>
                        <option value="transaction">{{ __('Transaction') }}</option>
                        <option value="finance">{{ __('Finance') }}</option>
                        <option value="management">{{ __('Management') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </select>

                    <select class="form-select form-select-sm" id="filterStatus" wire:model.live="filterStatus">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="in_progress">{{ __('In Progress') }}</option>
                        <option value="resolved">{{ __('Resolved') }}</option>
                        <option value="closed">{{ __('Closed') }}</option>
                    </select>
                </div>
            </div>

            {{-- Feedback List --}}
            <div class="feedback-list" id="feedbackList">
                @forelse ($chatRoomList as $item)
                    <div class="feedback-item {{ $chatRoomId == $item['nidchatroom'] ? 'active' : '' }}"
                        wire:click="selectChatRoom({{ $item['nidchatroom'] }})"
                        data-chatroom-id="{{ $item['nidchatroom'] }}"
                        data-user="{{ $item['applicant']->name ?? 'Unknown' }}"
                        data-menu="{{ $item['ctype'] }}" data-status="{{ $item['cstatus'] }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h6 class="feedback-title mb-0">
                                        {{ $item['creference'] ?? 'Complaint by ' . ($item['applicant']->name ?? 'Unknown') }}
                                    </h6>
                                    <span class="feedback-menu">{{ ucfirst($item['ctype']) }}</span>
                                </div>
                                <p class="feedback-user mb-0">
                                    {{ $item['applicant']->name ?? 'Unknown' }}
                                    <span class="user-role">({{ ucfirst($item['customer']->cnmcust ?? 'Customer') }})</span>
                                </p>
                                <p class="feedback-description text-truncate mb-0 text-capitalize">
                                    {{ $item['cissue'] ?? 'Other' }}
                                </p>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <span class="feedback-time d-block">
                                    {{ $item['last_message_at'] ? $item['last_message_at']->diffForHumans() : 'No messages' }}
                                </span>
                                @if ($item['unread_count'] > 0)
                                    <span class="unread-badge">{{ $item['unread_count'] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center p-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">{{ __('No feedbacks found') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Resizer Handle --}}
        <div class="resizer" id="resizer"></div>

        {{-- Right Side - Chat Content --}}
        <div class="chat-container flex-grow-1" id="chatContainer">
            @if (!$chatRoomId)
                {{-- Empty State --}}
                <div class="empty-state">
                    <div class="text-center">
                        <i class="bi bi-chat-dots display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">{{ __('Select a feedback to view conversation') }}</h5>
                        <p class="text-muted small">{{ __('Choose a feedback from the list to start chatting') }}</p>
                    </div>
                </div>
            @else
                {{-- Chat Content --}}
                <div class="chat-content">
                    {{-- Chat Header --}}
                    <div class="chat-header border-bottom p-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ $chatRoom->creference ?? 'Complaint by ' . ($chatRoom->applicant->name ?? 'Unknown') }} - {{ ucfirst($chatRoom->ctype) }}</h6>
                                <small class="text-muted">{{ $chatRoom->applicant->name ?? 'Unknown' }} - {{ ucfirst($chatRoom->applicant->customer->cnmcust ?? 'Customer') }}</small>
                            </div>
                            <div>
                                <span class="badge text-capitalize
                                    @if ($chatRoom->cstatus === 'in_progress')
                                        bg-warning
                                    @elseif ($chatRoom->cstatus === 'resolved')
                                        bg-success
                                    @elseif ($chatRoom->cstatus === 'closed')
                                        bg-secondary
                                    @endif
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $chatRoom->cstatus)) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @php
                        $autoCloseAt = $chatRoom->auto_close_at
                            ? \Carbon\Carbon::parse($chatRoom->auto_close_at)
                            : null;
                        $isAutoClosedByTime = $autoCloseAt && now()->greaterThanOrEqualTo($autoCloseAt);
                        $isAutoClosedPending = $isAutoClosedByTime && $chatRoom->cstatus === 'in_progress';
                        $isTicketLocked = in_array($chatRoom->cstatus, ['resolved', 'closed'], true) || $isAutoClosedPending;
                    @endphp

                    {{-- Chat Messages --}}
                    <div class="chat-messages" id="chatMessages"
                        data-first-unread-message-id="{{ $firstUnreadMessageId }}">
                        @php
                            $sortedMembers = $chatRoom->members->sortBy(function ($member) use ($chatRoom) {
                                // Aplicant
                                if ($member->id == $chatRoom->applicant->id) {
                                    return 1;
                                }

                                // Admins
                                if ($member->role === 'admin') {
                                    return 2;
                                }

                                // Others
                                return 3;
                            });
                        @endphp
                        {{-- System Header --}}
                        <div class="system-message">
                            <div class="system-card">
                                <i class="bi bi-megaphone"></i>
                                <div>
                                    <strong class="fs-8">Complaint Ticket Created</strong>
                                    <p class="mb-0 fs-6">
                                        {{ $chatRoom->applicant->name ?? 'User' }}
                                        - {{ $chatRoom->applicant->customer->cnmcust ?? 'Customer' }}
                                        on {{ \Carbon\Carbon::parse($chatRoom->created_at)->format('d/m/y H:i') }}
                                    </p>
                                    <small class="text-muted fs-6">
                                        Ref: {{ $chatRoom->creference }}
                                    </small>
                                    <div class="members-label fs-6">Participants</div>
                                    <ul class="members-list">
                                        @foreach($sortedMembers as $member)
                                            <li class="member-item">
                                                <span class="member-name fs-6">
                                                    {{ $member->name }}
                                                    @if($member->customer?->cnmcust)
                                                        - {{ $member->customer->cnmcust }}
                                                    @endif
                                                </span>
                                                <span class="member-role fs-6">
                                                    @if($member->id == $chatRoom->applicant->id)
                                                        Applicant
                                                    @elseif($member->role === 'admin')
                                                        Admin
                                                    @else
                                                        Member
                                                    @endif
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @if($chatRoom->auto_close_at && $chatRoom->cstatus === 'in_progress' && !$isAutoClosedByTime)
                            <div class="system-message mt-2">
                                <div class="system-card border-info">
                                    <i class="bi bi-info-circle"></i>
                                    <div>
                                        <small class="text-muted fs-6">
                                            This ticket will be automatically closed if there is no new message
                                            within <strong>2 days</strong> on
                                            {{ \Carbon\Carbon::parse($chatRoom->auto_close_at)->format('d/m/y H:i') }}.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Messages grouped by date --}}
                        @php
                            $grouped = collect($messages)->groupBy(function($m) {
                                return \Carbon\Carbon::parse($m['created_at'])->toDateString();
                            });
                        @endphp

                        @if($grouped->isEmpty())
                            <div class="system-message mt-3">
                                <i class="bi bi-chat-dots px-2"></i>
                                {{ __('No messages yet. Start the conversation!') }}
                            </div>
                        @else
                            @foreach($grouped as $date => $msgs)
                                @php
                                    $dt = \Carbon\Carbon::parse($date);
                                    $label = $dt->isToday() ? __('Today') : ($dt->isYesterday() ? __('Yesterday') : $dt->format('d/m/y'));
                                @endphp

                                <div class="date-separator my-2"><span>{{ $label }}</span></div>

                                @foreach($msgs as $message)
                                    <div class="message {{ $message['niduser'] == Auth::user()->id ? 'outgoing' : 'incoming' }}"
                                        data-message-id="{{ $message['nidmessage'] ?? '' }}">
                                        <div class="message-content">
                                            <div class="message-header">
                                                <span class="message-sender">{{ $message['user']['name'] }}</span>
                                                <span class="message-time">
                                                    {{ \Carbon\Carbon::parse($message['created_at'])->format('H:i') }}
                                                </span>
                                            </div>
                                            <div class="message-bubble">
                                                @if (!empty($message['cattachment_path']))
                                                    <img src="{{ asset('storage/' . $message['cattachment_path']) }}"
                                                        class="img-fluid rounded"
                                                        style="max-width: 250px; cursor:pointer;"
                                                        onclick="window.open(this.src)">
                                                @endif

                                                @if(!empty($message['cattachment_path']) && !empty($message['ctext']))
                                                    <p class="mt-0"></p>
                                                @endif

                                                @if (!empty($message['ctext']))
                                                    <p class="mb-0">{{ $message['ctext'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        @endif
                        {{-- System Footer --}}
                        @if(in_array($chatRoom->cstatus, ['resolved', 'closed']) || $isAutoClosedPending)
                            <div class="system-message mt-3">
                                <div class="system-card border-warning">
                                    <i class="bi bi-check-circle"></i>
                                    <div>
                                        <strong>
                                            @if($chatRoom->cstatus === 'resolved')
                                                Complaint Ticket Resolved
                                            @else
                                                Complaint Ticket Closed
                                            @endif
                                        </strong>

                                        <p class="mb-1 fs-6">
                                            This ticket has been
                                            {{ $isAutoClosedPending ? 'automatically' : '' }}
                                            <strong>{{ $chatRoom->cstatus === 'resolved' ? 'resolved' : 'closed' }}</strong> 
                                            by {{ $isAutoClosedPending ? 'System' : ($chatRoom->closedBy->name ?? 'System') }}.
                                        </p>

                                        @if($isAutoClosedPending)
                                            <div class="mt-1">
                                                <small class="text-muted fs-6">
                                                    Reason: Closed by system because there is no active activity in 2 days.
                                                </small>
                                            </div>
                                        @elseif(!empty($chatRoom->creason))
                                            <div class="mt-1">
                                                <small class="text-muted fs-6">
                                                    Reason: {{ $chatRoom->creason }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Chat Input --}}
                    <div class="chat-input border-top p-3">
                        <form wire:submit.prevent="sendMessage" enctype="multipart/form-data">
                            {{-- Preview Image --}}
                            <div class="mb-2" wire:loading wire:target="attachment">
                                <div class="d-inline-flex align-items-center gap-2 text-muted small">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    <span>{{ __('Loading photo preview...') }}</span>
                                </div>
                            </div>

                            <div class="mb-2" wire:loading.remove wire:target="attachment">
                                @if ($attachment)
                                    <img src="{{ $attachment->temporaryUrl() }}"
                                        class="img-thumbnail"
                                        style="max-height:150px;">
                                @endif
                            </div>
                            <div class="input-group mb-2">

                                {{-- Upload Button --}}
                                <label class="btn btn-outline-secondary mb-0">
                                    <i class="bi bi-image"></i>
                                    <input type="file"
                                        wire:model="attachment"
                                        accept="image/*"
                                        hidden
                                        {{ $isTicketLocked ? 'disabled' : '' }}>
                                </label>

                                {{-- Text Input --}}
                                <input type="text" 
                                    class="form-control"
                                    placeholder="{{ __('Type a message...') }}"
                                    wire:model="newMessage"
                                    autocomplete="off"
                                    {{ $isTicketLocked ? 'disabled' : '' }}>

                                {{-- Send Button --}}
                                <button class="btn btn-success" type="submit"
                                    {{ $isTicketLocked ? 'disabled' : '' }}>
                                    <i class="bi bi-send-fill"></i> {{ __('Send') }}
                                </button>
                            </div>
                            @php
                                $canManageStatus =
                                    Auth::id() === ($chatRoom->applicant->id ?? null)
                                    || Auth::user()?->role === 'admin';
                            @endphp
                            @if ($canManageStatus)
                                <div class="d-flex gap-2">
                                    @if (!$isTicketLocked)
                                        <button class="btn btn-sm btn-success" type="button"
                                            wire:click="updateStatus('resolved')">
                                            <i class="bi bi-check-circle"></i> {{ __('Mark as Resolved') }}
                                        </button>
                                    @endif
                                    @if (!$isTicketLocked)
                                        <button class="btn btn-sm btn-danger" type="button"
                                            wire:click="updateStatus('closed')">
                                            <i class="bi bi-x-circle"></i> {{ __('Close Conversation') }}
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>


<!-- Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" wire:ignore.self>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                    {{ __('Update Conversation Status') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-warning small">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ __('Please provide the reason before updating the conversation status.') }}
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        {{ __('Reason') }}
                        <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" rows="4" wire:model.defer="statusReason"
                        placeholder="{{ __('Please provide details...') }}"></textarea>
                    @error('statusReason')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('Cancel') }}
                </button>
                <button type="button" class="btn btn-warning" wire:click="updateStatus">
                    <i class="bi bi-send me-1"></i>
                    {{ __('Submit') }}
                </button>
            </div>
        </div>
    </div>
</div>
</div>
@push('styles')
    <style>
        /* Main Container */
        .feedback-sidebar {
            width: 380px;
            min-width: 280px;
            max-width: 600px;
            display: flex;
            flex-direction: column;
            background-color: #fff;
        }

        /* Resizer */
        .resizer {
            width: 5px;
            cursor: col-resize;
            background-color: #f0f0f0;
            transition: background-color 0.2s;
            position: relative;
        }

        .resizer:hover {
            background-color: #198754;
        }

        .resizer::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: -3px;
            right: -3px;
        }

        /* Sidebar Header */
        .sidebar-header {
            background-color: #f8f9fa;
        }

        /* Feedback List */
        .feedback-list {
            overflow-y: auto;
            flex: 1;
        }

        .feedback-item {
            padding: 8px 12px;
            border-bottom: 1px solid #e0e0e0;
            cursor: pointer;
            transition: background-color 0.15s;
        }

        .feedback-item:hover {
            background-color: #f8f9fa;
        }

        .feedback-item.active {
            background-color: #e9f5ef;
            border-left: 3px solid #198754;
        }

        .feedback-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #212529;
        }

        .feedback-menu {
            font-size: 0.7rem;
            background-color: #e9ecef;
            color: #495057;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 500;
        }

        .feedback-user {
            font-size: 0.75rem;
            color: #495057;
            line-height: 1.3;
        }

        .feedback-description {
            font-size: 0.75rem;
            color: #6c757d;
            line-height: 1.3;
        }

        .user-role {
            color: #6c757d;
            font-style: italic;
            font-size: 0.7rem;
        }

        .feedback-time {
            font-size: 0.7rem;
            color: #6c757d;
            white-space: nowrap;
            margin-bottom: 2px;
        }

        .unread-badge {
            background-color: #198754;
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 0.65rem;
            font-weight: 600;
            min-width: 18px;
            text-align: center;
            display: inline-block;
        }

        /* Chat Container */
        .chat-container {
            display: flex;
            flex-direction: column;
            background-color: #f5f5f5;
            position: relative;
        }

        /* Empty State */
        .empty-state {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            background-color: #fafafa;
        }

        /* Chat Content */
        .chat-content {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* Chat Messages */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
        }

        /* Messages */
        .message {
            display: flex;
            margin-bottom: 12px;
            width: 100%;
            padding: 0 1rem;
            margin-top: 0.5rem;
        }

        .message:first-child {
            margin-top: 1rem;
        }

        .message.incoming {
            justify-content: flex-start;
        }

        .message.outgoing {
            justify-content: flex-end;
        }

        .message-content {
            max-width: 600px;
            display: inline-flex;
            flex-direction: column;
        }

        .message.outgoing .message-content {
            align-items: flex-end;
        }

        .message-header {
            display: flex;
            gap: 8px;
            align-items: baseline;
            margin-bottom: 4px;
        }

        .message.outgoing .message-header {
            flex-direction: row-reverse;
        }

        .message-sender {
            font-size: 0.75rem;
            font-weight: 600;
            color: #495057;
        }

        .message-time {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .message-bubble {
            background-color: #f0f0f0;
            border-radius: 6px;
            padding: 8px 12px;
            word-wrap: break-word;
            width: fit-content;
            max-width: 100%;
        }

        .message.outgoing .message-bubble {
            background-color: #d4f4dd;
        }

        .message-bubble p {
            font-size: 0.875rem;
            line-height: 1.4;
        }

        /* Meta (Name + Time) */
        .meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.7rem;
            margin-bottom: 4px;
            color: #6c757d;
        }

        .name {
            font-weight: 600;
            color: #495057;
        }

        /* System Message */
        .system-message {
            display: flex;
            justify-content: center;
            margin: 15px 0;
            font-size: 0.8rem;
            color: #6c757d;
        }

        .system-card {
            display: flex;
            gap: 10px;
            background: #f8f9fa;
            padding: 12px 14px;
            border-radius: 10px;
            max-width: 600px;
            text-align: left;
        }

        /* Members */
        .members-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #888;
            margin-bottom: 4px;
            margin-top: 4px;
        }

        .members-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .member-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid #f1f1f1;
            gap: 2px;
        }

        .member-item:last-child {
            border-bottom: none;
        }

        .member-name {
            font-size: 0.7rem;
            font-weight: 300;
            color: #6c757d;
        }

        .member-role {
            font-size: 0.75rem;
            color: #999;
        }

        .system-card i {
            font-size: 1rem;
            margin-top: 2px;
        }

        /* Chat Input */
        .chat-input {
            background-color: #fff;
            border-top: 2px solid #e0e0e0;
        }

        .chat-input .form-control {
            border: 1px solid #dee2e6;
        }

        .chat-input .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
        }

        /* Scrollbar Styling */
        .feedback-list::-webkit-scrollbar,
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .feedback-list::-webkit-scrollbar-track,
        .chat-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .feedback-list::-webkit-scrollbar-thumb,
        .chat-messages::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .feedback-list::-webkit-scrollbar-thumb:hover,
        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Date Separator (minimalist) */
        .date-separator {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 12px 0;
            color: #6c757d;
            font-size: 0.75rem;
        }

        .date-separator::before,
        .date-separator::after {
            content: '';
            flex: 1 1 0;
            height: 1px;
            background: #e9ecef;
        }

        .date-separator span {
            padding: 0 8px;
            white-space: nowrap;
            text-transform: capitalize;
        }

        /* Mobile Responsive */
        @media (max-width: 991.98px) {
            .feedback-sidebar {
                width: 100%;
                max-width: 100%;
            }

            .resizer {
                display: none;
            }

            .chat-container {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 1040;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            }

            .chat-container.show {
                transform: translateX(0);
            }

            .message-content {
                max-width: 80%;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
        const statusModalElement = document.getElementById('statusModal');
        const statusModal = statusModalElement ? new bootstrap.Modal(statusModalElement) : null;

        Livewire.on('open-status-modal', () => statusModal?.show());
        Livewire.on('close-status-modal', () => statusModal?.hide());

        setTimeout(() => {
            if (window.Echo?.socketId()) {
                Livewire.dispatch('setSocketId', { socketId: window.Echo.socketId() });
            }
        }, 500);

        // Resizer
        const resizer = document.getElementById('resizer');
        const sidebar = document.getElementById('feedbackSidebar');
        if (resizer && sidebar) {
            let isResizing = false, startX = 0, startWidth = 0;
            resizer.addEventListener('mousedown', (e) => {
                isResizing = true;
                startX = e.clientX;
                startWidth = sidebar.offsetWidth;
                document.body.style.cursor = 'col-resize';
                document.body.style.userSelect = 'none';
            });
            document.addEventListener('mousemove', (e) => {
                if (!isResizing) return;
                const width = startWidth + (e.clientX - startX);
                if (width >= 280 && width <= 600) sidebar.style.width = width + 'px';
            });
            document.addEventListener('mouseup', () => {
                if (isResizing) {
                    isResizing = false;
                    document.body.style.cursor = '';
                    document.body.style.userSelect = '';
                }
            });
        }

        // Scroll
        const chatState = {
            initializedChatrooms: new Set(),
            messageCountByChatroom: {},
        };

        const scrollToBottom = (el) => requestAnimationFrame(() => el.scrollTop = el.scrollHeight);

        const syncChatScrollPosition = () => {
            const chatMessages = document.getElementById('chatMessages');
            if (!chatMessages) return;

            const currentChatroomId = @this.get('chatRoomId');
            if (!currentChatroomId) return;

            const messages = chatMessages.querySelectorAll('.message');
            const messageCount = messages.length;
            const latestMessage = messages[messageCount - 1];
            const previousCount = chatState.messageCountByChatroom[currentChatroomId] ?? 0;
            const isFirst = !chatState.initializedChatrooms.has(currentChatroomId);
            const hasNew = messageCount > previousCount;
            const isOutgoing = latestMessage?.classList.contains('outgoing');

            if (isFirst || (hasNew && isOutgoing)) {
                scrollToBottom(chatMessages);
                chatState.initializedChatrooms.add(currentChatroomId);
            }

            chatState.messageCountByChatroom[currentChatroomId] = messageCount;
        };

        Livewire.hook('morph.updated', syncChatScrollPosition);

        // =============================================
        // Reverb - subscribe berdasarkan event dispatch
        // dari Livewire, bukan morph.updated
        // =============================================
        let activeChatroomChannel = null;

        const subscribeToChatroom = (chatroomId) => {
            if (!window.Echo || !chatroomId) return;
            if (activeChatroomChannel === chatroomId) return; // sudah subscribe, skip

            // Tinggalkan channel lama
            if (activeChatroomChannel) {
                window.Echo.leave(`chatroom.${activeChatroomChannel}`);
                console.log('Left chatroom:', activeChatroomChannel);
            }

            activeChatroomChannel = chatroomId;

            window.Echo.private(`chatroom.${chatroomId}`)
                .listen('.chat.message.sent', (data) => {
                    console.log('📨 Message received:', data);
                    const el = document.querySelector('[wire\\:id]');
                    if (el) {
                        const wireId = el.getAttribute('wire:id');
                        Livewire.find(wireId).call('refreshChatState');
                    }
                })
                .error((err) => console.error('Channel error:', err));

            console.log('Subscribed to chatroom:', chatroomId);
        };

        // Subscribe ke chatroom yang sudah aktif saat halaman pertama load
        const initialId = @this.get('chatRoomId');
        if (initialId) subscribeToChatroom(initialId);

        // Listen event dari Livewire ketika user pindah chatroom
        Livewire.on('chatRoomSelected', ({ chatRoomId }) => {
            console.log('🎯 chatRoomSelected fired:', chatRoomId);
            subscribeToChatroom(chatRoomId);
        });
    });
    </script>
@endpush
