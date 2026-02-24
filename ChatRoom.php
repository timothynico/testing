<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $table = 'tchatrooms';
    protected $primaryKey = 'nidchatroom';
    protected $fillable = [
        'nidchatroom',
        'nidcomplicant',
        'creference',
        'ctype',
        'cissue',
        'cstatus',
        'cdescription',
        'curl',
        'nidclose',
        'creason',
        'created_at',
        'updated_at'
    ];

    // Complicant
    public function applicant()
    {
        return $this->belongsTo(User::class, 'nidcomplicant');
    }

    // All members
    public function members()
    {
        return $this->belongsToMany(
            User::class,
            'tchatroomdtl',
            'nidchatroom',
            'niduser'
        )
        ->withPivot('nidlastreadmessage')
        ->withTimestamps();
    }

    // All messages in this chatroom
    public function messages()
    {
        return $this->hasMany(Message::class, 'nidchatroom', 'nidchatroom');
    }

    // Unread messages count for user
    public function unreadCountFor($userId)
    {
        $member = $this->members()
            ->where('niduser', $userId)
            ->first();

        if (!$member) return 0;

        $lastRead = $member->pivot->nidlastreadmessage ?? 0;

        return $this->messages()
            ->where('nidmessage', '>', $lastRead)
            ->where('niduser', '!=', $userId)
            ->count();
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'nidclose');
    }

    public function getAutoCloseAtAttribute()
    {
        $lastActivity = $this->messages()
            ->latest()
            ->value('created_at') 
            ?? $this->created_at;

        return \Carbon\Carbon::parse($lastActivity)->addDays(2);
    }
}
