<?php

declare(strict_types=1);

namespace Bot\Intents;

use FondBot\Conversation\Activators\Activator;
use FondBot\Conversation\Intent;
use FondBot\Drivers\ReceivedMessage;
use FondBot\Helpers\Str;
use Symfony\Component\Process\Process;
use Bot\Services\Storage;

class ExecCommandIntent extends Intent
{
    /**
     * Intent activators.
     *
     * @return Activator[]
     */
    public function activators(): array
    {
        return [
            $this->exact('/exec'),
            $this->contains('Выполни'),
        ];
    }

    public function run(ReceivedMessage $message): void
    {
        $text = $message->getText();

        $command = strstr($text, ' ');

        if (! $command) {
            $this->sendMessage('Не передана команда, которую надо выполнить');
        } else {
            $command = trim((string) $command);
            $this->exec($command);
        }
    }

    public function exec($command)
    {
        $cwd = 'c:\\www\\remotebot';

        switch (true) {
            case $this->startsWith($command, 'git.exe'):
                $command = '"c:\\Program Files\\Git\\git-bash.exe" -c "' . $command . '"';
                break;
            case $this->startsWith($command, 'php artisan'):
                $cwd = 'c:\\www\\bcs';
                break;
            case $this->startsWith($command, 'toolbelt'):
                $cwd = 'c:\\www\\remotebot';
                $command = 'php bin\\' . $command;
                break;

            default:
                break;
        }

        $this->sendMessage('Выполняю ' . $command . ' в директории ' . $cwd);

        $process = new Process($command, $cwd);
        $process->run();

        $result = $process->getOutput() ?: $process->getErrorOutput();
        $result = mb_convert_encoding($result, 'UTF-8');

        try {
            if (mb_strlen($result) > 2000) {
                $attachment = resolve(Storage::class)->create($result);
                $this->sendAttachment($attachment);
            } else {
                $this->sendMessage($result ?: '-');
            }
        } catch (\Exception $e) {
            $this->sendMessage('Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * @todo Вынести в хелпер
     * @param type|string $value
     * @return type
     */
    public function startsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }
}
