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
                        placeholder="{{ __('Search feedback...') }}" id="searchFeedback">
                </div>

                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="filterMenu">
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

                    <select class="form-select form-select-sm" id="filterStatus">
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
                                    <h6 class="feedback-title mb-0">{{ $item['creference'] }}</h6>
                                    <span class="feedback-menu">{{ ucfirst($item['ctype']) }}</span>
                                </div>
                                <p class="feedback-user mb-0">
                                    {{ $item['applicant']->name ?? 'Unknown' }}
                                    <span class="user-role">({{ ucfirst($item['applicant']->role ?? 'User') }})</span>
                                </p>
                                <p class="feedback-description text-truncate mb-0">
                                    {{ $item['cdescription'] ?? 'No description' }}
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
                        <p class="mt-2">{{ __('No chatrooms found') }}</p>
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
                                <h6 class="mb-0">{{ $chatRoom->creference }}</h6>
                                <small class="text-muted">{{ $chatRoom->applicant->name ?? 'Unknown' }} - {{ ucfirst($chatRoom->ctype) }}</small>
                            </div>
                            <div>
                                <span
                                    class="badge bg-{{ $chatRoom->cstatus === 'open' ? 'success' : ($chatRoom->cstatus === 'resolved' ? 'info' : 'secondary') }}">
                                    {{ ucfirst($chatRoom->cstatus) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Chat Messages --}}
                    <div class="chat-messages" id="chatMessages">
                        @forelse ($messages as $message)
                            <div class="message {{ $message['niduser'] == Auth::id() ? 'outgoing' : 'incoming' }}">
                                <div class="message-content">
                                    <div class="message-header">
                                        <span class="message-sender">{{ $message['user']['name'] }}</span>
                                        <span class="message-time">
                                            {{ \Carbon\Carbon::parse($message['created_at'])->format('H:i') }}
                                        </span>
                                    </div>
                                    <div class="message-bubble">
                                        <p class="mb-0">{{ $message['ctext'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="system-message mt-3">
                                <i class="bi bi-chat-dots"></i>
                                {{ __('No messages yet. Start the conversation!') }}
                            </div>
                        @endforelse
                    </div>

                    {{-- Chat Input --}}
                    <div class="chat-input border-top p-3">
                        <form wire:submit.prevent="sendMessage">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" placeholder="{{ __('Type a message...') }}"
                                    wire:model="newMessage" autocomplete="off"
                                    {{ $chatRoom->cstatus === 'closed' ? 'disabled' : '' }}>
                                <button class="btn btn-success" type="submit"
                                    {{ $chatRoom->cstatus === 'closed' ? 'disabled' : '' }}>
                                    <i class="bi bi-send-fill"></i> {{ __('Send') }}
                                </button>
                            </div>
                            <div class="d-flex gap-2">
                                @if ($chatRoom->cstatus !== 'resolved' && $chatRoom->cstatus !== 'closed')
                                    <button class="btn btn-sm btn-success" type="button"
                                        wire:click="updateStatus('resolved')">
                                        <i class="bi bi-check-circle"></i> {{ __('Mark as Resolved') }}
                                    </button>
                                @endif
                                @if ($chatRoom->cstatus !== 'closed')
                                    <button class="btn btn-sm btn-danger" type="button"
                                        wire:click="updateStatus('closed')">
                                        <i class="bi bi-x-circle"></i> {{ __('Close Conversation') }}
                                    </button>
                                @endif
                            </div>
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
                <button type="button" class="btn btn-warning" wire:click="submitStatusUpdate">
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
            flex-direction: row;
            justify-content: flex-start;
        }

        .message.outgoing {
            flex-direction: row-reverse;
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

        /* System Message */
        .system-message {
            text-align: center;
            margin: 12px 0;
            font-size: 0.8rem;
            color: #6c757d;
        }

        .system-message i {
            margin-right: 4px;
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

            Livewire.on('open-status-modal', () => {
                statusModal?.show();
            });

            Livewire.on('close-status-modal', () => {
                statusModal?.hide();
            });

            setTimeout(() => {
                if (window.Echo && window.Echo.socketId) {
                    const socketId = window.Echo.socketId();
                    console.log('Socket ID:', socketId);

                    Livewire.dispatch('setSocketId', { socketId });
                }
            }, 500);

            // Resizer functionality
            const resizer = document.getElementById('resizer');
            const sidebar = document.getElementById('feedbackSidebar');

            if (resizer && sidebar) {
                let isResizing = false;
                let startX = 0;
                let startWidth = 0;

                resizer.addEventListener('mousedown', function(e) {
                    isResizing = true;
                    startX = e.clientX;
                    startWidth = sidebar.offsetWidth;
                    document.body.style.cursor = 'col-resize';
                    document.body.style.userSelect = 'none';
                });

                document.addEventListener('mousemove', function(e) {
                    if (!isResizing) return;

                    const width = startWidth + (e.clientX - startX);
                    const minWidth = 280;
                    const maxWidth = 600;

                    if (width >= minWidth && width <= maxWidth) {
                        sidebar.style.width = width + 'px';
                    }
                });

                document.addEventListener('mouseup', function() {
                    if (isResizing) {
                        isResizing = false;
                        document.body.style.cursor = '';
                        document.body.style.userSelect = '';
                    }
                });
            }

            // Auto scroll to bottom of messages
            Livewire.hook('morph.updated', ({
                component,
                cleanup
            }) => {
                const chatMessages = document.getElementById('chatMessages');
                if (chatMessages) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            });

            // Listen for feedback selection to setup Echo listener
            let echoChannel = null;

            Livewire.on('feedback-selected', (event) => {
                const feedbackId = event.feedbackId;

                // Check if Echo is available
                if (!window.Echo) {
                    console.warn('⚠️ Echo not available, real-time updates disabled');
                    return;
                }

                // Leave previous channel if exists
                if (echoChannel) {
                    console.log('Leaving channel:', `feedback.${echoChannel}`);
                    window.Echo.leave(`feedback.${echoChannel}`);
                }

                // Join new feedback channel
                echoChannel = feedbackId;
                console.log('Joining channel:', `feedback.${feedbackId}`);

                window.Echo.private(`feedback.${feedbackId}`)
                    .subscribed(() => {
                        console.log('✅ Successfully subscribed to channel:', `feedback.${feedbackId}`);
                    })
                    .listen('MessageSent', (e) => {
                        console.log('🔔 New message received:', e);
                        // Call Livewire method to reload messages
                        @this.call('onMessageReceived');
                    })
                    .error((error) => {
                        console.error('❌ Echo error:', error);
                    });
            });
        });
    </script>
@endpush
