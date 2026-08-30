<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PromptCreate
{
    /*
     * Промпт о событиях
     * */
    public function events($message): string
    {
        $events = DB::connection('mysql_ffar')
            ->table('event_calendar')
            ->where('status', 1)
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        $eventsContext = '';

        $num = 1;
        foreach ($events as $event) {
            $eventsContext .= "№ {$num} Название: {$event->name}, место: {$event->event_place}, начало: " . date('d.m.Y', strtotime($event->start_date)) . ", окончание: " . date('d.m.Y', strtotime($event->end_date));
            $num++;
        }

        return "Ответь на вопрос пользователя: '{$message}', используя эти данные: {$eventsContext}";
    }

    /*
     *
     * */
    public function news($message): string
    {
        $news = DB::connection('mysql_ffar')
            ->table('news')
            ->where('is_published', 1)
            ->orderByDesc('id') // Сортируем: сначала новые
            ->limit(3)
            ->get(['title', 'slug']);

        $newsContext = '';

        $num = 1;
        foreach ($news as $new) {
            $newsContext .= "№ {$num} Название: {$new->title}, ссылка: https://ffarsport-ckp.ru/news/{$new->slug}/";
            $num++;
        }

        return "Ответь на вопрос пользователя: '{$message}', используя эти данные: {$newsContext}. В конце пропиши ссылки на каждую новость в красивом HTML коде";

    }

    public function courses($message): string
    {
        $courses = DB::connection('mysql_ffar')
            ->table('courses')
            ->where('publish', 1)
            ->inRandomOrder()
            ->limit(3)
            ->get(['name', 'slug']);

        $coursesContext = '';

        $num = 1;
        foreach ($courses as $course) {
            $coursesContext .= "№ {$num} Название: {$course->name}, ";
            $num++;
        }

        $coursesContext = substr($coursesContext, 0, -2);
        $coursesContext .= 'Ссылка на каталог с курсами ФФАР: https://ffar.server-technologies.ru/academy/courses/catalog';

        return "Ответь на вопрос пользователя: '{$message}', используя эти данные: {$coursesContext}.";
    }
}
