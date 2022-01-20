<?php

declare(strict_types=1);

use Bunny\Channel;
use Bunny\Client;
use Meteia\Configuration\Configuration;
use Meteia\RabbitMQ\Bunny\BunnyExchange;
use Meteia\RabbitMQ\Bunny\BunnyQueue;
use Meteia\RabbitMQ\Contracts\Exchange;
use Meteia\RabbitMQ\Contracts\Queue;
use React\EventLoop\LoopInterface;

return [
    Client::class => function (Configuration $config): Client {
        $hostname = $config->string('RABBITMQ_HOST', 'rabbitmq');
        $port = $config->int('RABBITMQ_PORT', 5672);
        $username = $config->string('RABBITMQ_USERNAME', 'guest');
        $password = $config->string('RABBITMQ_PASSWORD', 'guest');
        $virtualHost = $config->string('RABBITMQ_VIRTUALHOST', '/');
        $timeout = $config->int('RABBITMQ_TIMEOUT', 1);
        $heartbeat = $config->float('RABBITMQ_HEARTBEAT', 60.0);
        $keepAlive = $config->boolean('RABBITMQ_KEEPALIVE', false);

        return new Client([
            'host' => $hostname,
            'port' => $port,
            'vhost' => $virtualHost,
            'user' => $username,
            'password' => $password,
            'timeout' => $timeout,
            'heartbeat' => $heartbeat,
            'keepAlive' => $keepAlive,
        ]);
    },
    \Bunny\Async\Client::class => function (LoopInterface $loop, Configuration $config): Bunny\Async\Client {
        $hostname = $config->string('RABBITMQ_HOST', 'rabbitmq');
        $port = $config->int('RABBITMQ_PORT', 5672);
        $username = $config->string('RABBITMQ_USERNAME', 'guest');
        $password = $config->string('RABBITMQ_PASSWORD', 'guest');
        $virtualHost = $config->string('RABBITMQ_VIRTUALHOST', '/');
        $timeout = $config->int('RABBITMQ_TIMEOUT', 1);
        $heartbeat = $config->float('RABBITMQ_HEARTBEAT', 60.0);
        $keepAlive = $config->boolean('RABBITMQ_KEEPALIVE', false);

        return new \Bunny\Async\Client($loop, [
            'host' => $hostname,
            'port' => $port,
            'vhost' => $virtualHost,
            'user' => $username,
            'password' => $password,
            'timeout' => $timeout,
            'heartbeat' => $heartbeat,
            'keepAlive' => $keepAlive,
        ]);
    },
    Channel::class => function (Client $client): Channel {
        $client->connect();

        return $client->channel();
    },
    Exchange::class => BunnyExchange::class,
    Queue::class => BunnyQueue::class,
];
