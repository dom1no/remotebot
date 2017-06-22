<?php

declare(strict_types=1);

namespace Bot\Interactions;

use FondBot\Conversation\Interaction;
use FondBot\Drivers\ReceivedMessage;
use Bot\Services\PullService;

class PullProjectInteraction extends Interaction
{
    /**
     * Run interaction.
     *
     * @param ReceivedMessage $message
     */
    public function run(ReceivedMessage $message): void
    {
        $this->sendMessage('Какой проект обновить?');
    }

    /**
     * Process received message.
     *
     * @param ReceivedMessage $reply
     */
    public function process(ReceivedMessage $reply): void
    {
        $text = $reply->getText();
        $text = mb_strtolower($text);

        $this->update($text);
    }

    /**
     * Обновление проекта
     * @param string $text
     * @return void
     */
    public function update(string $text)
    {
        if (! $project = $this->getProject($text)) {
            $this->sendMessage('Такого проекта я не знаю');
            $this->restart();
            return;
        }

        $this->sendMessage('Ок. обновляю ' . $project['title']);

        list($success, $message) = resolve(PullService::class)->pull($project['path']);

        if ($success) {
            $this->sendMessage('Обновил!' .  $message);
        } else {
            $this->sendMessage('Не удалось обновить: ' . $message ?? 'что-то пошло не так');
        }
    }

    /**
     * Узнаем путь к проекту, который нужно обновить
     * @param string $text
     * @return string|null
     */
    public function getProject(string $text)
    {
        // TODO: config
        $projects = [
            [
                'names' => [
                    'back',
                    'бэк',
                    'be',
                ],
                'path' => 'c:\\www\\bcs\\',
                'title' => 'Бэк',
            ],
            [
                'names' => [
                    'front',
                    'фронт',
                    'fe',
                ],
                'path' => 'c:\\www\\other\\fe\\',
                'title' => 'Фронт',
            ],
            [
                'names' => [
                    'bot',
                    'бот',
                    'be',
                ],
                'path' => 'c:\\www\\remotebot\\',
                'title' => 'Бота',
            ],
        ];

        foreach ($projects as $project) {
            if (in_array($text, $project['names']))
                return $project;
        }

        return null;
    }
}
