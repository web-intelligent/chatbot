<?php

namespace App\Http\Controllers;

use App\Events\ReadMessage;
use App\Events\SupportSendMessage;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    //
    public function index()
    {
        $chats = Chat::where('status', 'open')->with('lastMessage')->withCount('unreadMessages')->orderBy('created_at')->get();

        $meta['title'] = 'Панель управления технической поддержки';

        return view('support.index', compact('chats', 'meta'));
    }

    /*
     * Получение сообщений по чату
     * */
    public function getMessages(Request $request)
    {
        if ($request->ajax()) {
            $chat = Chat::find($request->chat_id);

            if (!$chat) {
                return response()->json(['error' => 'Чат не найден'], 404);
            }

            $chat->messages()
                ->where('is_read', 0)
                ->where('sender_type', 'user') // важно
                ->update([
                    'is_read' => 1
                ]);

            $messages = $chat->messages()
                ->orderBy('created_at', 'asc') // 'asc' для хронологического порядка
                ->get();

            event(new ReadMessage($chat));

            return view('support.ajax.messages', compact('messages'));
        }

        abort(403);
    }

    /*
     * Пометка сообщения прочитанным
     * */

    public function readMessage(Request $request)
    {
        if ($request->isMethod('POST') && $request->ajax()) {
            if (is_array($request->message_id)) {
                $validated = $request->validate([
                    'message_id' => 'required|array',
                    'message_id.*' => 'required|integer|exists:messages,id',
                ]);
            } else {
                $validated = $request->validate([
                    'message_id' => 'required|integer|exists:messages,id',
                ]);
            }

            $update = Message::where('id', $validated['message_id'])->update(['is_read' => 1]);
            $msg = Message::find($validated['message_id']);

            if ($update) {
                broadcast(new SupportSendMessage($msg, 'read'));
                return response()->json(['success' => true]);
            }

        }

        abort(403);
    }

    /*
     * Заполнение шапки
     * */

    public function fillHeader(Request $request)
    {
        if ($request->isMethod('POST') && $request->ajax()) {
            $validated = $request->validate([
                'chat_id' => 'required|integer|exists:chats,id'
            ]);

            $chat = Chat::find($validated['chat_id']);

            return view('support.ajax.fill_header', compact('chat'));
        }

        abort(403);
    }


    /*
     * Отправка сообщения
     * */
    public function sendMessage(Request $request)
    {
        if ($request->isMethod('POST') && $request->ajax()) {
            $validated = $request->validate([
                'chat_id' => 'required|integer|exists:chats,id',
                'message' => 'required|string',
            ], [
                'chat_id.required' => 'Сначала нужно выбрать чат, в котором Вы пишите сообщение'
            ]);

            $message = trim(preg_replace('/\s+/u', ' ', strip_tags($validated['message'])));

            $chat = Chat::find($validated['chat_id']);

            if ($chat) {
                $arr = [
                    'chat_id' => $chat->id,
                    'sender_type' => 'support',
                    'message' => $message,
                    'sender_id' => Auth::id(),
                ];

                $msg = Message::create($arr);

                broadcast(new SupportSendMessage($msg, 'message'));

                return response()->json([
                    'status' => true,
                    'message' => $msg,
                ], JSON_UNESCAPED_UNICODE);
            }
        }

        return response()->json([
            'status' => false,
        ], 403);
    }
}
