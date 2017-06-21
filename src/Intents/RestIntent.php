<?php

declare(strict_types=1);

namespace Bot\Intents;

use FondBot\Conversation\Activators\Activator;
use FondBot\Conversation\Intent;
use FondBot\Drivers\ReceivedMessage;
use Bot\Interactions\RestUrlInteraction;
use Bot\Interactions\RestParamsInteraction;


class RestIntent extends Intent
{
    /**
     * Intent activators.
     *
     * @return Activator[]
     */
    public function activators(): array
    {
        return [
            $this->exact('/rest'),
            $this->contains('Рест'),
        ];
    }

    public function run(ReceivedMessage $message): void
    {
        $this->jump(RestUrlInteraction::class);
        // $this->jump(RestParamsInteraction::class);
    }
}
