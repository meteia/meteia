<?php

declare(strict_types=1);

use Bunny\Channel;
use Bunny\ChannelInterface;
use Bunny\Client;
use Meteia\AdvancedMessageQueuing\AmbientMessageScopeSource;
use Meteia\AdvancedMessageQueuing\Configuration\CommandsExchangeName;
use Meteia\AdvancedMessageQueuing\Configuration\DelayedCommandsExchangeName;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Configuration\Configuration;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\MessageScopeSource;

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
    Channel::class => static function (Client $client): ChannelInterface {
        $client->connect();

        return $client->channel();
    },
    CommandsExchangeName::class => static fn(
        Configuration $configuration,
        ApplicationNamespace $applicationNamespace,
    ): CommandsExchangeName => new CommandsExchangeName($configuration->string(
        'METEIA_AMQ_COMMANDS_EXCHANGE_NAME',
        $applicationNamespace . '.Commands',
    )),
    DelayedCommandsExchangeName::class => static fn(
        Configuration $configuration,
        ApplicationNamespace $applicationNamespace,
    ): DelayedCommandsExchangeName => new DelayedCommandsExchangeName($configuration->string(
        'METEIA_AMQ_DELAYED_COMMANDS_EXCHANGE_NAME',
        $applicationNamespace . '.DelayedCommands',
    )),
    AmbientMessageScopeSource::class =>
        static fn(MessageScope $scope): AmbientMessageScopeSource => new AmbientMessageScopeSource($scope),
    MessageScopeSource::class => static fn(AmbientMessageScopeSource $source): MessageScopeSource => $source,
];
