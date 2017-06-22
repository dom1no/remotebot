<?php

declare(strict_types=1);

namespace Bot\Providers;

use FondBot\Toolbelt\Command;
use FondBot\Toolbelt\ToolbeltServiceProvider as BaseToolbeltServiceProvider;
use Bot\Commands\WebhookStart;
use Bot\Commands\WebhookRestart;

class ToolbeltServiceProvider extends BaseToolbeltServiceProvider
{
    /**
     * Console commands.
     *
     * @return Command[]
     */
    public function commands(): array
    {
        return [
            new WebhookStart,
            new WebhookRestart,
        ];
    }
}
