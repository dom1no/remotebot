<?php

declare(strict_types=1);

namespace Bot\Commands;

use FondBot\Toolbelt\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use GuzzleHttp\Client;
use FondBot\Contracts\Cache;

class WebhookStart extends Command
{
    protected function configure(): void
    {
        $this->setName('webhook:start')
            ->setDescription('Start server and set webhook for telegram bot')
            ->addArgument('url', InputArgument::OPTIONAL, 'Webhook url')
            ->addOption('tunnel', 't', InputOption::VALUE_OPTIONAL, 'Tunnel defined in ngrok.yml', 'bot back');
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

        $this->backTunnel($process);

        if ($process && $process->isRunning()) {
            $this->success('Server running');

            $lastRestartTime = $this->getLastRestartTime();
            $restart = false;

            while ($process->isRunning()) {
                sleep(3);

                if ($restart = $this->shouldRestart($lastRestartTime)) {
                    break;
                }
            }

            if ($restart) {
                $this->line('Server restarting');
                $this->handle();
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
        $command = 'ngrok start ' . $tunnel . ' --config=' . env('NGROK_CONFIG');

        $process = new Process($command);
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
     * @param string $bot
     * @return string
     */
    public function getUrl(Process $process, string $name = 'bot')
    {
        $url = '';
        $guzzle = new Client;

        while (! $url && $process->isRunning()) {
            $tunnels = $guzzle->get('http://localhost:4030/api/tunnels');

            $tunnels = collect(json_decode((string) $tunnels->getBody(), true)['tunnels']);
            $url = $tunnels->where('name', $name)->first()['public_url'];
        }

        if (! $url) {
            $cache = resolve(Cache::class);

            $restartAttempts = $cache->get('ngrok.restart_attemts', 0);

            if ($restartAttempts < 5) {
                $process->stop();
                sleep(2);

                $message = 'Ngrok ' . $name . ' not running. Try restart...';
                $this->error($message);
                $this->sendMessage($message);

                $cache->store('ngrok.restart_attemts', ++$restartAttempts);

                $this->handle();
            } else {
                $cache->forget('ngrok.restart_attemts');

                $message = 'Ngrok ' . $name . ' not running. Exit!';
                $this->error('Ngrok ' . $name . ' not running. Exit!');
                $this->sendMessage($message);
            }
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
        $response = $guzzle->get($apiUrl, ['proxy' => env('PROXY')]);
        $response = json_decode((string) $response->getBody());

        $type = $response->ok ? 'success' : 'error';

        $this->{$type}($response->description);
    }

    /**
     * Время когда последний раз нужно было рестартовать сервер
     * @return int|null
     */
    public function getLastRestartTime()
    {
        return resolve(Cache::class)->get('ngrok.restart') ?: null;
    }

    /**
     * Нужно ли рестартовать сервер
     * @param int $lastRestartTime
     * @return bool
     */
    public function shouldRestart(int $lastRestartTime = null)
    {
        return $this->getLastRestartTime() != $lastRestartTime;
    }

    /**
     * Отправка url для тунеля бэка
     * @param Process $process
     * @return void
     */
    public function backTunnel(Process $process)
    {
        $url = $this->getUrl($process, 'back');

        $status = $this->sendMessage('Тунель для бэка: ' . $url);

        $this->{$status}('Tunnel for back started: ' . $url);
    }

    /**
     * Отправка сообщения в телеграм
     * @param string $message
     * @param int $chatId
     * @return void
     */
    public function sendMessage(string $message, int $chatId = null)
    {
        $apiUrl = 'https://api.telegram.org/bot';
        $apiUrl .= env('TELEGRAM_TOKEN');
        $apiUrl .= '/sendMessage';

        $payload = [
            'chat_id' => $chatId ?: env('TELEGRAM_CHAT_ID'),
            'text' => $message,
        ];

        $guzzle = new Client;
        $response = $guzzle->post($apiUrl, ['json' => $payload, 'proxy' => env('PROXY')]);

        $response = json_decode((string) $response->getBody());

        $status = $response->ok ? 'success' : 'error';

        return $status;
    }
}
