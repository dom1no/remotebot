<?php

namespace Bot\Services;

use GuzzleHttp\Client;

/**
* Сервис по обновлению проектов
*/
class PullService
{
    /**
     * @param string $path
     * @return mixed
     */
    public function pull(string $path)
    {
        try {
            chdir($path);

            $command = 'git reset --hard HEAD && git pull';
            $message = exec('"c:\\Program Files\\Git\\git-bash.exe" -c "' . $command . '; sleep 3"');

            return [true, $message];
        } catch (Exception $e) {
            return [false, $e->getMessage()];
        }
    }
}