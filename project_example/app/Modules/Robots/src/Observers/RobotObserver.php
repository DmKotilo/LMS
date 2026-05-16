<?php

namespace Robots\Observers;

use Illuminate\Support\Facades\Log;
use Robots\Models\Robot;

class RobotObserver
{
    /**
     * Handle the Robot "saved" event.
     * Обновляет файл robots.txt после сохранения модели (создание или обновление)
     */
    public function saved(Robot $robot): void
    {
        Log::info('RobotObserver: событие saved вызвано', [
            'robot_id' => $robot->id,
            'content_length' => strlen($robot->content ?? ''),
            'was_recently_created' => $robot->wasRecentlyCreated,
            'was_changed' => $robot->wasChanged()
        ]);

        $this->updateRobotsFile($robot);
    }

    /**
     * Handle the Robot "updated" event.
     * Обновляет файл robots.txt после обновления модели
     */
    public function updated(Robot $robot): void
    {
        Log::info('RobotObserver: событие updated вызвано', [
            'robot_id' => $robot->id,
            'content_length' => strlen($robot->content ?? ''),
            'was_changed_content' => $robot->wasChanged('content')
        ]);

        $this->updateRobotsFile($robot);
    }

    /**
     * Обновляет файл robots.txt
     */
    protected function updateRobotsFile(Robot $robot): void
    {
        // Перезагружаем модель из БД, чтобы убедиться, что у нас актуальные данные
        $robot->refresh();

        Log::info('RobotObserver: обновление файла robots.txt', [
            'robot_id' => $robot->id,
            'content_length' => strlen($robot->content ?? ''),
            'content_preview' => substr($robot->content ?? '', 0, 100)
        ]);

        $result = $robot->saveToFile();

        Log::info('RobotObserver: saveToFile завершен', [
            'robot_id' => $robot->id,
            'result' => $result
        ]);
    }
}

