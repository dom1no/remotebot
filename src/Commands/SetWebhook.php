<?php

declare(strict_types=1);

namespace Bot\Commands;

use FondBot\Toolbelt\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use GuzzleHttp\Client;

class SetWebhook extends Command
{
    protected function configure(): void
    {
        $this->setName('telegram:webhook')
            ->setDescription('Set webhook for telegram bot')
            ->addArgument('url', InputArgument::OPTIONAL, 'Webhook url')
            ->addOption('tunnel', 't', InputOption::VALUE_OPTIONAL, 'Tunnel defined in ngrok.yml', 'local');
    }

    public function handle(): void
    {
        $url = $this->getArgument('url');
        $tunnel = $this->getOption('tunnel');

        if (! $url) {
            $process = $this->startNgrok($tunnel);
            $url = $this->getUrl($process);
        }

        $this->setWebhook($url);

        if ($process && $process->isRunning()) {
            $this->success('Server running');

            while ($process->isRunning()) {
                sleep(3);
            }

            $this->error('Server was stopped');
        } else {
            $this->error('Server not running');
        }
    }

    /**
     * Старт ngrok
     * @param string $tunnel
     * @return Process
     */
    public function startNgrok(string $tunnel)
    {
        $process = new Process('ngrok start ' . $tunnel . ' --config=' . env('NGROK_CONFIG'));
        $result = $process->start();

        if ($process->isStarted()) {
            $this->success('ngrok started successfully');
        } else {
            $this->error('ngrok not started');
            exit;
        }

        return $process;
    }

    /**
     * Получение url запущенного тунеля
     * @param Process $process
     * @return string
     */
    public function getUrl(Process $process)
    {
        $url = '';
        $guzzle = new Client;

        while (! $url && $process->isRunning()) {
            $tunnels = $guzzle->get('http://localhost:4040/api/tunnels');

            $tunnels = collect(json_decode((string) $tunnels->getBody(), true)['tunnels']);

            $url = $tunnels->where('proto', 'https')->first()['public_url'];
        }

        if (! $url) {
            $this->error('Ngrok not running');
            exit;
        }

        return $url;
    }

    /**
     * Вызов реста установки webhook для бота
     * @param string $url
     * @return void
     */
    public function setWebhook(string $url)
    {
        $apiUrl = 'https://api.telegram.org/bot';
        $apiUrl .= env('TELEGRAM_TOKEN');
        $apiUrl .= '/setWebhook?url=';
        $apiUrl .= $url;
        $apiUrl .= '/channels/telegram';

        $guzzle = new Client;
        $response = $guzzle->get($apiUrl);
        $response = json_decode((string) $response->getBody());

        $type = $response->ok ? 'success' : 'error';

        $this->{$type}($response->description);
    }
}
