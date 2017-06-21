<?php

declare(strict_types=1);

namespace Bot\Interactions;

use FondBot\Conversation\Interaction;
use FondBot\Drivers\ReceivedMessage;
use FondBot\Contracts\Cache;
use FondBot\Templates\Attachment;
use Bot\Services\RestSendService;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;


class RestParamsInteraction extends Interaction
{
    /**
     * Run interaction.
     *
     * @param ReceivedMessage $message
     */
    public function run(ReceivedMessage $message): void
    {
        $this->sendMessage('Какие параметры?');
    }

    /**
     * Process received message.
     *
     * @param ReceivedMessage $reply
     */
    public function process(ReceivedMessage $reply): void
    {
        if ($reply->hasAttachment()) {
            $attachment = $reply->getAttachment();

            $type = $attachment->getType();

            if ($type != 'file') {
                $this->sendMessage('Пришли мне параметры текстовым файлом или сообщением.');
                $this->restart();
                return;
            }

            $path = $attachment->getPath();
            $metadata = $attachment->getMetadata();
            $message = $type . ' - ' . $path . PHP_EOL . $metadata;

            $json = $metadata;
        } else {
            $json = $reply->getText();
        }

        try {
            $params = collect(json_decode($json, true));
        } catch (Exception $e) {
            $this->sendMessage('Невалидный json ты прислал');
            $this->restart();
            return;
        }

        if ($params->isEmpty()) {
            $message = 'Вызову запрос с пустым json';
        } else {
            $message = 'Вызову запрос с параметрами: ' . $params->toJson();
        }

        $this->sendMessage($message);

        $this->sendApiRequest($params->toArray());
    }

    /**
     * Отправка запроса к api
     * @param array $params
     * @return type
     */
    public function sendApiRequest($params)
    {
        $cache = resolve(Cache::class);

        $method = $cache->get('rest.method');
        $url = $cache->get('rest.url');

        $response = resolve(RestSendService::class)->send($method, $url, $params);

        if ($response !== false) {
            if (mb_strlen($response) > 2000) {
                $attachment = $this->createAttachment($response);

                $this->sendAttachment($attachment);
                // $this->sendMessage($response);
            } else {
                $this->sendMessage($response);
            }
        }

        $cache->forget('rest');
    }

    public function createAttachment(string $response)
    {
        $path = resources('attachments');

        $adapter = new Local($path);
        $filesystem = new Filesystem($adapter);

        $name = md5(microtime()) . '.json';
        $filesystem->write($name, $response);

        return (new Attachment)->setPath($path . '/' . $name)->setType(Attachment::TYPE_FILE);
    }
}
