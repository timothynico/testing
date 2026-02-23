<div class="row">

    {{-- ============================= --}}
    {{-- SIDEBAR LIST CHAT ROOM --}}
    {{-- ============================= --}}
    <div class="col-4 border-end" style="height: 600px; overflow-y: auto;">

        <h5 class="mb-3">Chat Rooms</h5>

        @forelse($chatRoomList as $room)
            <div
                wire:click="selectChatRoom({{ $room['nidchatroom'] }})"
                style="cursor:pointer; padding:10px; border-bottom:1px solid #eee;"
            >
                <div class="fw-bold">
                    {{ $room['creference'] }}
                </div>

                <small>
                    {{ $room['applicant']->name ?? '-' }}
                </small>

                <br>

                <small class="text-muted">
                    {{ $room['cstatus'] }}
                </small>

                <div class="d-flex justify-content-between">
                    <small class="text-muted">
                        @if($room['last_message_at'])
                            {{ $room['last_message_at']->diffForHumans() }}
                        @endif
                    </small>

                    @if($room['unread_count'] > 0)
                        <span class="badge bg-danger">
                            {{ $room['unread_count'] }}
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-muted">Tidak ada chat room</p>
        @endforelse

    </div>


    {{-- ============================= --}}
    {{-- AREA CHAT --}}
    {{-- ============================= --}}
    <div class="col-8">

        @if($chatRoomId)

            <div class="border-bottom mb-3 pb-2">
                <h5>
                    {{ $chatRoom->creference ?? '' }}
                </h5>
            </div>

            {{-- MESSAGE LIST --}}
            <div style="height:450px; overflow-y:auto;" class="mb-3">

                @forelse($messages as $msg)
                    <div class="mb-2">
                        <strong>{{ $msg['user']['name'] }}</strong>
                        <br>
                        {{ $msg['ctext'] }}
                        <br>
                        <small class="text-muted">
                            {{ \Carbon\Carbon::parse($msg['created_at'])->format('H:i') }}
                        </small>
                    </div>
                @empty
                    <p class="text-muted">Belum ada pesan</p>
                @endforelse

            </div>

            {{-- INPUT MESSAGE --}}
            <form wire:submit.prevent="sendMessage">
                <div class="d-flex gap-2">
                    <input
                        type="text"
                        class="form-control"
                        wire:model="newMessage"
                        placeholder="Ketik pesan..."
                    >

                    <button class="btn btn-primary">
                        Kirim
                    </button>
                </div>
            </form>

        @else
            <div class="text-muted mt-5 text-center">
                Pilih chat room dulu
            </div>
        @endif

    </div>

</div>
