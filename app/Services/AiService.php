<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiService
{
    public function ask(string $prompt): string
    {
        $response = Http::timeout(120)
            ->withHeaders([
                'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                'Content-Type' => 'application/json',
            ])
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                //                'model' => 'deepseek/deepseek-chat-v3',
                'model' => 'openrouter/free',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ]
                ]
            ]);

        if (!$response->successful()) {
            throw new \Exception($response->body());
        }

        return $response->json('choices.0.message.content');
    }


    public function detectTopic(string $message): string
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                'Content-Type' => 'application/json',
            ])
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'openrouter/free',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Определи категорию вопроса. Допустимые категории: events, news, courses, documents, general. Ответь только названием категории. Вопрос: {$message}"
                    ]
                ],
                'max_tokens' => 5
            ]);

        return trim(
            strtolower(
                $response->json('choices.0.message.content')
            )
        );
    }
}
