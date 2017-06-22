<?php

declare(strict_types=1);

namespace Bot\Intents;

use FondBot\Conversation\Activators\Activator;
use FondBot\Conversation\Intent;
use FondBot\Drivers\ReceivedMessage;
use FondBot\Contracts\Cache;

class StoreTokenIntent extends Intent
{
    /**
     * Intent activators.
     *
     * @return Activator[]
     */
    public function activators(): array
    {
        return [
            $this->exact('/token'),
            $this->contains('Запомни токен'),
        ];
    }

    public function run(ReceivedMessage $message): void
    {
        $text = $message->getText();

        $words = explode(' ', $text);

        $token = trim(array_pop($words));

        if ($token) {
            resolve(Cache::class)->store('session.token', $token);
            $this->sendMessage('Ок, запомнил токен: ' . $token);
        } else {
            $this->sendMessage('Ты не прислал токен');
        }
    }
}
