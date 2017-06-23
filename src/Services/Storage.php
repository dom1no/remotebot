<?php

namespace Bot\Services;

use FondBot\Templates\Attachment;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

/**
* Создание файла для телеграма
*/
class Storage
{
    /**
     * Создание файла для телеграма
     * @param string $response
     * @return Attachment
     */
    public function create(string $response)
    {
        $path = resources('attachments');

        $adapter = new Local($path);
        $filesystem = new Filesystem($adapter);

        $name = md5(microtime()) . '.txt';
        $filesystem->write($name, $response);

        return (new Attachment)->setPath($path . '/' . $name)->setType(Attachment::TYPE_FILE);
    }
}