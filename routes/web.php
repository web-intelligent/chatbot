<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\UserController;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    $meta['title'] = "Чат-бот технической поддержки ФФАР";
    return view('home', compact('meta'));
})->name('home');

Route::get('/welcome', function () {
    $meta['title'] = "Welcome";
    return view('welcome', compact('meta'));
});

Route::controller(ReactionController::class)->group(function () {
    Route::post('/reaction', 'reaction')->name('reaction');
    Route::post('/is-typing', 'userType')->name('user.type');
});

Route::get('/chats', function () {
    $meta['title'] = "Чаты";

    $chats = Chat::where('user_id', Auth::id())->get();
    dump($chats);
    return view('chats', compact('meta', 'chats'));

})->middleware('auth')->name('chats');


Route::prefix('chat')->middleware('auth')->name('chat.')->group(function () {
    Route::controller(ChatController::class)->group(function () {
        Route::post('/user-send', 'userSend')->name('user.send');
        Route::post('/user-typing', 'userTyping')->name('user.typing');

        Route::post('/send-message', 'sendMessage')->name('send.message');
        Route::get('/{chat}', 'index')->name('index');

        Route::post('/read-message', 'readMessage')->name('read.message');
    });
});

Route::prefix('support')->middleware(['auth', 'operator'])->name('support.')->group(function () {
    Route::controller(SupportController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/get-messages', 'getMessages')->name('get_messages');
        Route::post('/read-message', 'readMessage')->name('read_message');
        Route::post('/fill-header', 'fillHeader')->name('fill_header');

        Route::post('/send-message', 'sendMessage')->name('send.message');
    });
});

Route::controller(UserController::class)->group(function () {
   Route::get('/login', 'login')->name('login');
    Route::post('/registration', 'registration')->name('registration');
    Route::post('/auth', 'auth')->name('auth');
    Route::get('/logout', 'logout')->name('logout');
});
