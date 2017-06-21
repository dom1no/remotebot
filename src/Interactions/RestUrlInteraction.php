<?php

declare(strict_types=1);

namespace Bot\Interactions;

use FondBot\Conversation\Interaction;
use FondBot\Drivers\ReceivedMessage;
use Bot\Interactions\RestParamsInteraction;
use FondBot\Contracts\Cache;

class RestUrlInteraction extends Interaction
{
    /**
     * Run interaction.
     *
     * @param ReceivedMessage $message
     */
    public function run(ReceivedMessage $message): void
    {
        $this->sendMessage('Какой Url?');
    }

    /**
     * Process received message.
     *
     * @param ReceivedMessage $reply
     */
    public function process(ReceivedMessage $reply): void
    {
        $text = $reply->getText();

        $method = (string) strstr($text, ' ', true);
        $method = strtoupper(trim($method));

        $url = (string) strstr($text, ' ');
        $url = trim($url);

        if (! in_array($method, ['GET', 'POST', 'PUT', 'PATCH'])) {
            $this->sendMessage('Неверный метод');
            $this->restart();
            return;
        }

        $cache = resolve(Cache::class);

        $cache->store('rest.method', $method);
        $cache->store('rest.url', $url);

        $this->sendMessage('Ок, вызову рест ' . $url .  ' методом ' . $method);
        $this->jump(RestParamsInteraction::class);
    }
}
