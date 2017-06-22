<?php

declare(strict_types=1);

namespace Bot\Intents;

use FondBot\Conversation\Activators\Activator;
use FondBot\Conversation\Intent;
use FondBot\Drivers\ReceivedMessage;
use Bot\Interactions\PullProjectInteraction;

class PullProjectIntent extends Intent
{
    /**
     * Intent activators.
     *
     * @return Activator[]
     */
    public function activators(): array
    {
        return [
            $this->exact('/pull'),
            $this->contains('Обнови'),
            $this->contains('обнови'),
        ];
    }

    public function run(ReceivedMessage $message): void
    {
        $this->jump(PullProjectInteraction::class);
    }
}
