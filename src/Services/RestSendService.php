<?php

namespace Bot\Services;

use FondBot\Contracts\Cache;
use GuzzleHttp\Client;

/**
* Сервис по отправке запросов к api
*/
class RestSendService
{
    /**
     * Формирование и отправка запроса
     * @param type|string $value
     * @return mixed
     */
    public function send(string $method, string $url, array $params = [], string $base_uri = null)
    {
        $base_uri = $base_uri ?: env('REST_HOST');

        $client = new Client(['base_uri' => $base_uri]);

        $options = [];
        $options['json'] = $params;

        $token = resolve(Cache::class)->get('token');

        $options['headers'] = ['Authorization' => $token];
        $options['http_errors'] = false;
        $options['connect_timeout'] = 5;
        $options['timeout'] = 60;

        if ($method == 'GET' && count($params) > 0) {
            $url .= '?' . http_build_query($params);
        }

        try {
            $response = $client->request($method, $url, $options);
            return (string) $response->getBody();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}