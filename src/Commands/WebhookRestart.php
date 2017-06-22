<?php

declare(strict_types=1);

namespace Bot\Commands;

use FondBot\Toolbelt\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use GuzzleHttp\Client;
use FondBot\Contracts\Cache;

class WebhookRestart extends Command
{
    protected function configure(): void
    {
        $this->setName('webhook:restart')
            ->setDescription('Restart ngrok server for telegram bot');
    }

    public function handle(): void
    {
        resolve(Cache::class)->store('ngrok.restart', time());

        $this->success('Server restarted');
    }
}
