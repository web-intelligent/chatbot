<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'chat_id',
        'sender_type',
        'sender_id',
        'message',
        'guest_id'
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
