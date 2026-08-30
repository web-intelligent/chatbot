<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'assigned_to',
        'guest_id'
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unreadMessages()
    {
        return $this->hasMany(Message::class)
            ->where('is_read', 0)
            ->where('sender_type', 'user'); // например: считаем только от пользователя
    }
}
