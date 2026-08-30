<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class OllamaService
{
    public function ask(string $message): string
    {
        // Получаем мероприятия
        $events = DB::connection('mysql_ffar')
            ->table('event_calendar')
            ->where('status', 1)
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        // Собираем контекст
        $eventsContext = '';
        $num = 1;
        foreach ($events as $event) {

            $eventsContext .= '№ '.$num  .' Название: '. $event->name .', Место: ' . $event->event_place .' Начало: ' . date('d.m.Y', strtotime($event->start_date)) . ', Окончание: ' . date('d.m.Y', strtotime($event->end_date)) .'. ';
            $num++;
        }

        // Формируем общий запрос для модели
        $prompt = "Ты консультант Федерации фитнес-аэробики России. Для ответа используй только следующую информацию. ({$eventsContext}). Если ответа нет в предоставленных данных, сообщи, чего тебе не хватило для создания ответа. Вопрос пользователя: {$message}." ;


        $response = Http::timeout(240)
            ->post('http://127.0.0.1:11434/api/generate', [
                'model' => 'qwen2.5:3b',
                'prompt' => $prompt, // ← здесь уже не $message
                'stream' => false,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Ошибка Ollama: ' . $response->body());
        }

        return $response->json('response');
    }
}
