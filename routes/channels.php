<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('support-dashboard', function ($user) {
    if (in_array($user->role, [1, 2])) {
        return true;
    }

    return false;
});

/*
 * Прочитанные сообщения
 * */
Broadcast::channel('read_message.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);

    if (!$chat) {
        return false;
    }

    if ($chat->user_id !== Auth::id()) return false;

    return true;
});


/*
 * Прочитанные сообщения пользователем для техподдержки
 * */
Broadcast::channel('user_read_message.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);

    if (!$chat) {
        return false;
    }

//    if ($chat->user_id !== Auth::id()) return false;

    return true;
});


/*
 * Канал связи пользователя с технической поддержкой в чате
 * */
Broadcast::channel('support_send_message.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);
    if (!$chat) {
        return false;
    }

    if ($chat->user_id !== Auth::id()) return false;

    return true;

});

/*
 * Канала прослушивания прочитанного сообщения от пользователя к техподдержка
 * */
Broadcast::channel('support_read_message.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);
    if (!$chat) {
        return false;
    }

    if ($chat->user_id !== Auth::id()) return false;

    return true;

});


Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);

    if (!$chat) {
        return false;
    }

    // тут можешь добавить свою проверку доступа
    // например только операторы или участники чата

    return [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->role,
        'chat' => $chat,
    ];
});


Broadcast::channel('online-users', function ($user) {
    if (!$user) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});
