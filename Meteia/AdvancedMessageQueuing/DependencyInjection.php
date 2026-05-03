<?php

declare(strict_types=1);

use Bunny\Channel;
use Bunny\Client;
use Meteia\AdvancedMessageQueuing\Configuration\CommandsExchangeName;
use Meteia\Application\ApplicationNamespace;
use Meteia\Configuration\Configuration;
use React\EventLoop\LoopInterface;

$connectionOptions = static fn(Configuration $config): array => [
    'host' => $config->string('RABBITMQ_HOST', '127.0.0.1'),
    'port' => $config->int('RABBITMQ_PORT', 5672),
    'vhost' => $config->string('RABBITMQ_VIRTUALHOST', '/'),
    'user' => $config->string('RABBITMQ_USERNAME', 'guest'),
    'password' => $config->string('RABBITMQ_PASSWORD', 'guest'),
    'timeout' => $config->int('RABBITMQ_TIMEOUT', 1),
    'heartbeat' => $config->float('RABBITMQ_HEARTBEAT', 60.0),
    'keepAlive' => $config->boolean('RABBITMQ_KEEPALIVE', false),
];

return [
    Client::class => static fn(Configuration $config): Client => new Client($connectionOptions($config)),
    Bunny\Async\Client::class => static fn(
        LoopInterface $loop,
        Configuration $config,
    ): Bunny\Async\Client => new Bunny\Async\Client($loop, $connectionOptions($config)),
    Channel::class => static function (Client $client): Channel {
        $client->connect();

        return $client->channel();
    },
    CommandsExchangeName::class => static fn(
        Configuration $configuration,
        ApplicationNamespace $applicationNamespace,
    ): CommandsExchangeName => new CommandsExchangeName($configuration->string(
        'METEIA_RABBITMQ_COMMANDS_EXCHANGE_NAME',
        $applicationNamespace . '.Commands',
    )),
];
