<?php

declare(strict_types=1);

namespace Bot\Providers;

use Bot\Intents\ExampleIntent;
use Bot\Intents\RestIntent;
use Bot\Intents\PullProjectIntent;
use Bot\Intents\StoreTokenIntent;
use Bot\Intents\ExecCommandIntent;
use FondBot\Conversation\FallbackIntent;
use FondBot\Conversation\IntentServiceProvider as BaseIntentServiceProvider;

class IntentServiceProvider extends BaseIntentServiceProvider
{
    /**
     * Define intents.
     *
     * @return string[]
     */
    public function intents(): array
    {
        return [
            ExampleIntent::class,
            RestIntent::class,
            PullProjectIntent::class,
            StoreTokenIntent::class,
            ExecCommandIntent::class,
        ];
    }

    /**
     * Define fallback intent.
     *
     * @return string
     */
    public function fallbackIntent(): string
    {
        return FallbackIntent::class;
    }
}
