<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\NewChatCreated;
use App\Events\ReadMessage;
use App\Events\UserTypeEvent;
use App\Http\Requests\TopicDetectionRequest;
use App\Models\Chat;
use App\Models\Message;
use App\Services\AiService;
use App\Services\OllamaService;
use App\Services\OpenAiService;
use App\Services\PromptCreate;
use App\Services\PromtCreate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;

class ChatController extends Controller
{
    //
    private function getGuestId()
    {
        $guestId = request()->cookie('guest_id');

        if (!$guestId) {
            $guestId = (string) Str::uuid();

            Cookie::queue(
                Cookie::make(
                    'guest_id',
                    $guestId,
                    60 * 24 * 365 // 1 год
                )
            );
        }

        return $guestId;
    }

    /*
     * Когда пользователь печатает
     * */
    public function userTyping(Request $request)
    {
        $validated = $request->validate([
            'chat_id' => 'integer|required|exists:chats,id',
        ]);

        event(new UserTypeEvent($validated['chat_id']));

    }


    public function index(Chat $chat) {

        $user = Auth::user();

        if ($user->id != $chat->user_id) abort(403);

        $messages = $chat->messages()->orderBy('created_at')->get();

        $meta = [
            'title' => 'Чат с технической поддержкой'
        ];

        return view('chat.index', compact('user', 'messages', 'chat', 'meta'));
    }

    public function sendMessage(Request $request)
    {
        $user = Auth::user();

        if ($request->isMethod('POST')) {
            $validated = $request->validate([
                'chat_id' => 'required|integer|exists:chats,id',
                'message' => [
                    'required',
                    'string',
                    'min:1',
                    'max:1000',
                    'not_regex:/<[^>]*>/', // Защита от HTML/XML инъекций
                    'not_regex:/\\b(?:DROP|DELETE|INSERT|UPDATE|ALTER)\\b/i', // Защита от SQL инъекций
                ]
            ]);

            $message = trim(preg_replace('/\s+/u', ' ', strip_tags($validated['message'])));

            $chat = Chat::find($validated['chat_id']);

            if ($chat) {
                $arr = [
                    'chat_id' => $chat->id,
                    'sender_type' => 'user',
                    'message' => $message,
                ];
                if(Auth::check() && !is_null($chat->user_id)) {
                    $arr['sender_id'] = $chat->user_id;
                } else {
                    if ($chat->guest_id != $this->getGuestId()) {
                        // Тут вывести ошибку
                        dd('Тут вывести ошибку');
                    }
                    $arr['guest_id'] = $chat->guest_id;
                }

                $msg = Message::create($arr);


                // Вызываем сервис
                $openAI = app(OpenAIService::class);
                $topic = $openAI->detectTopic($message);


                $prompt_create = new PromptCreate();

                $prompt = $message;

                switch ($topic) {

                    case 'events':
                        $prompt = $prompt_create->events($message);
                        break;
                    case 'news':
                        $prompt = $prompt_create->news($message);
                        break;

                    case 'courses':
                        $prompt = $prompt_create->courses($message);
                        break;

                    case 'documents':
                        // documents
                        break;

                    default:
                        // общий ответ ИИ
                }

                $count_messages = Message::where('chat_id', $chat->id)->count();
                if ($count_messages > 1) {
                    $answer = $openAI->withHistory($prompt, $chat->id);
                } else {
                    $answer = $openAI->ask($prompt);
                }

                $aiMessage = Message::create([
                    'chat_id' => $chat->id,
                    'sender_type' => 'bot',
                    'message' => $answer,
                ]);

                broadcast(new MessageSent($msg))->toOthers();

                return response()->json([
                    'status' => true,
                    'message' => $msg,
                    'ai_message' => $aiMessage, // Ответ бота
                ], JSON_UNESCAPED_UNICODE);

            } else {
                $guestId = $this->getGuestId();

                $chat = Chat::create([
                    'user_id' => $user,
                    'guest_id' => $guestId,
                    'status' => 'open',
                ]);

                dd('тут дописать');

                broadcast(new NewChatCreated($chat, $message))->toOthers();
            }

        }

        abort(403);
    }

    public function userSend(Request $request)
    {
        $validated = $request->validate([
            'user_message' => 'required|string|max:500',
        ]);

        $messageText = trim(preg_replace('/\s+/u', ' ', strip_tags($validated['user_message'])));

        $user = Auth::user() ?? null;

        // 👉 определяем идентификатор
        $guestId = null;

        if (!$user) {
            $guestId = $this->getGuestId();
        }

        // 🔍 ищем чат
        $chatQuery = Chat::query()
            ->whereIn('status', ['open', 'pending']);

        if ($user) {
            $chatQuery->where('user_id', $user->id);
        } else {
            $chatQuery->where('guest_id', $guestId);
        }

        $chat = $chatQuery->latest()->first();


        // 🆕 создаём если нет
        if (!$chat) {
            $chat = Chat::create([
                'user_id' => (!is_null($user)) ? $user->id : null,
                'guest_id' => $guestId,
                'status' => 'open',
                'assigned_to' => 0
            ]);

            broadcast(new NewChatCreated($chat, $messageText))->toOthers();
        }

        // 💬 сообщение
        if ($user) {
            $arr = [
                'chat_id' => $chat->id,
                'sender_type' => 'user',
                'sender_id' => $user->id,
                'message' => $messageText,
            ];
        } else {
            $arr = [
                'chat_id' => $chat->id,
                'sender_type' => 'user',
                'guest_id' => $guestId,
                'message' => $messageText,
            ];
        }
        $message = Message::create($arr);

        $ai = app(OllamaService::class);

        $answer = $ai->ask($messageText);

        $aiMessage = Message::create([
            'chat_id' => $chat->id,
            'sender_type' => 'bot',
            'message' => $answer,
        ]);

//        broadcast(new MessageSent($message))->toOthers();
//
        return response()->json([
            'user' => $user,
            'status' => true,
            'message' => $message,
            'chat_id' => $chat->id
        ], JSON_UNESCAPED_UNICODE);

    }


    public function readMessage(Request $request)
    {
        if (!$request->isMethod('POST') || !$request->ajax()) {
            abort(403);
        }

        $validated = $request->validate([
            'chat_id' => 'required|integer|exists:chats,id',
            'message_id' => 'required',
        ]);

        // Приводим к массиву
        $messageIds = (array) $request->message_id;

        // Проверяем все элементы массива
        validator(
            ['message_id' => $messageIds],
            [
                'message_id' => 'required|array|min:1',
                'message_id.*' => 'required|integer|exists:messages,id',
            ]
        )->validate();

        // Получаем только те сообщения, которые ещё не прочитаны
        $updatedIds = Message::whereIn('id', $messageIds)
            ->where('is_read', 0)
            ->pluck('id')
            ->toArray();

        // Если обновлять нечего
        if (empty($updatedIds)) {
            return response()->json([
                'success' => true,
                'updated_ids' => []
            ]);
        }

        // Обновляем
        Message::whereIn('id', $updatedIds)
            ->update([
                'is_read' => 1
            ]);

        $chat = Chat::findOrFail($validated['chat_id']);

        // Отправляем событие техподдержке
        broadcast(
            new ReadMessage(
                $chat,
                $updatedIds
            )
        )->toOthers();

        return response()->json([
            'success' => true,
            'updated_ids' => $updatedIds
        ]);
    }


}
