<?php

namespace App\Services;

use App\Models\Message;

class OpenAiService
{

    public function detectTopic(string $message): string
    {

        $client = \OpenAI::factory()
            ->withApiKey(env('OPENAI_API_KEY_GPT_MINI'))
            ->withBaseUri('https://api.timeweb.ai/v1')
            ->make();

        $response = $client->chat()->create([
            'model'    => 'openai/gpt-5.4-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Ты - классификатор вопросов. Твоя задача - определить категорию вопроса и ответить только одним словом - названием категории. Допустимые категории: events, news, courses, documents, general. Никаких других слов, пояснений или знаков препинания.'
                ],
                [
                    'role' => 'user',
                    'content' => $message
                ],
            ],
            'temperature' => 0.1,  // Минимальная креативность для точной классификации
            'max_tokens' => 20,    // Достаточно для одного слова
            'stream' => false, // Отключаем потоковый режим
        ]);

        return $response->choices[0]->message->content ?? 'general';

    }

    public function ask(string $prompt): string
    {
        $client = \OpenAI::factory()
            ->withApiKey(env('OPENAI_API_KEY_GPT_MINI'))
            ->withBaseUri('https://api.timeweb.ai/v1')
            ->make();

        $response = $client->chat()->create([
            'model'    => 'openai/gpt-5.4-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Тебя зовут Полли. Ты - сотрудник технической поддержки Федерации фитнес-аэробики России, который помогает пользователям чата технической поддержки. Ты понимаешь и используешь в общении только русский и английский языки. Если пользователь обращается на другом языке, вежливо попроси его сформулировать запрос на русском или английском. Твоя главная задача - помогать пользователям находить ответы, касающиеся аккредитации региональных федераций, обучению судей, проведения мероприятий, прохождения курсов ФФАР и любой другой деятельности, которой занимается Федерация фитнес-аэробики России. Ты должен предоставлять точные, понятные и полные ответы. Сам ничего не придумывай. Общайся с пользователями вежливо и доброжелательно, обращайся на «Вы» с большой буквы. Если ты не знаешь ответ на вопрос, то ответь так: Я не располагаю данными по Вашему вопросу, поэтому передаю вопрос сотрудникам ФФАР'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ],
            ],
            'temperature' => 0.1,  // Минимальная креативность для точной классификации
            'max_tokens' => 1000,    // Достаточно для одного слова
            'stream' => false, // Отключаем потоковый режим
        ]);

        return $response->choices[0]->message->content;

    }

    public function withHistory(string $prompt, int $chatId): string
    {
        $history = Message::where('chat_id', $chatId)->get(['sender_type', 'message']);

        $history_array = [];
        foreach ($history as $message) {
            if ($message->sender_type == 'user') {
                $history_array[] = ['role' => 'user', 'content' => $message->message];
            } else {
                $history_array[] = ['role' => 'assistant', 'content' => $message->message];
            }
        }


        $client = \OpenAI::factory()
            ->withApiKey(env('OPENAI_API_KEY_GPT_MINI'))
            ->withBaseUri('https://api.timeweb.ai/v1')
            ->make();


        $response = $client->chat()->create([
            'model'    => 'openai/gpt-5.4-mini',
            'messages' => array_merge(
                [  // Системный промпт
                    ['role' => 'system', 'content' => 'Тебя зовут Полли. Ты - сотрудник технической поддержки Федерации фитнес-аэробики России, который помогает пользователям чата технической поддержки. Ты понимаешь и используешь в общении только русский и английский языки. Если пользователь обращается на другом языке, вежливо попроси его сформулировать запрос на русском или английском. Твоя главная задача - помогать пользователям находить ответы, касающиеся аккредитации региональных федераций, обучению судей, проведения мероприятий, прохождения курсов ФФАР и любой другой деятельности, которой занимается Федерация фитнес-аэробики России. Ты должен предоставлять точные, понятные и полные ответы. Сам ничего не придумывай. Общайся с пользователями вежливо и доброжелательно, обращайся на «Вы» с большой буквы. Если ты не знаешь ответ на вопрос, то ответь так: Я не располагаю данными по Вашему вопросу, поэтому передаю вопрос сотрудникам ФФАР']
                ],
                $history_array,  // ← Распаковываем историю
                [  // Текущее сообщение пользователя
                    ['role' => 'user', 'content' => $prompt]
                ]
            ),
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'stream' => false,
        ]);

        return $response->choices[0]->message->content;

    }

}
