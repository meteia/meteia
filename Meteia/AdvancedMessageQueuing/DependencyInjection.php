<?php

declare(strict_types=1);

use Bunny\Channel;
use Bunny\Client;
use Meteia\AdvancedMessageQueuing\Configuration\CommandsExchangeName;
use Meteia\Configuration\Configuration;
use React\EventLoop\LoopInterface;

return [
    Client::class => static function (Configuration $config): Client {
        $hostname = $config->string('RABBITMQ_HOST', '127.0.0.1');
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
    \Bunny\Async\Client::class => static function (LoopInterface $loop, Configuration $config): Bunny\Async\Client {
        $hostname = $config->string('RABBITMQ_HOST', '127.0.0.1');
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
    Channel::class => static function (Psr\Log\LoggerInterface $log, Client $client): Channel {
        try {
            $client->connect();

            return $client->channel();
        } catch (\Throwable $e) {
            $log->warning('Failed to connect to RabbitMQ: ' . $e->getMessage(), ['exception' => $e]);

            exit(1);
        }
    },
    CommandsExchangeName::class => static fn (
        Configuration $configuration,
    ): CommandsExchangeName => new CommandsExchangeName(
        $configuration->string('METEIA_RABBITMQ_COMMANDS_EXCHANGE_NAME', 'commands'),
    ),
];
