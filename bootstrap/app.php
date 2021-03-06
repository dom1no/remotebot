<?php

require __DIR__.'/../vendor/autoload.php';

$container = new League\Container\Container;

$kernel = FondBot\Application\Factory::create($container);

$container->addServiceProvider(new Bot\Providers\AppServiceProvider);
$container->addServiceProvider(new Bot\Providers\CacheServiceProvider);
$container->addServiceProvider(new Bot\Providers\ChannelServiceProvider);
$container->addServiceProvider(new Bot\Providers\FilesystemServiceProvider);
$container->addServiceProvider(new Bot\Providers\IntentServiceProvider);
$container->addServiceProvider(new Bot\Providers\LogServiceProvider);
$container->addServiceProvider(new Bot\Providers\QueueServiceProvider);