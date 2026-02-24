<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ChatRoomDetail;

Broadcast::channel('chatroom.{roomId}', function ($user, $roomId) {
    return ChatRoomDetail::where('nidchatroom', $roomId)
        ->where('niduser', $user->id)
        ->exists();
});
