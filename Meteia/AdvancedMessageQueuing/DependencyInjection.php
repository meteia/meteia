<?php

declare(strict_types=1);

use Bunny\Channel;
use Bunny\ChannelInterface;
use Bunny\Client;
use Meteia\AdvancedMessageQueuing\AmbientMessageScopeSource;
use Meteia\AdvancedMessageQueuing\Bunny\BunnyConnectionOptions;
use Meteia\AdvancedMessageQueuing\Bunny\BunnyRequestResources;
use Meteia\AdvancedMessageQueuing\Configuration\CommandsExchangeName;
use Meteia\AdvancedMessageQueuing\Management\BunnyRabbitMqManagement;
use Meteia\AdvancedMessageQueuing\Management\IsolatedBunnyRabbitMqManagement;
use Meteia\AdvancedMessageQueuing\Management\RabbitMqManagement;
use Meteia\AdvancedMessageQueuing\Management\VHostName;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\RequestResources;
use Meteia\Configuration\Configuration;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\MessageScopeSource;

return [
    BunnyConnectionOptions::class => static fn(Configuration $config): BunnyConnectionOptions => new BunnyConnectionOptions($config),
    Client::class => static fn(BunnyConnectionOptions $options): Client => $options->client(),
    Channel::class => static fn(Client $client): ChannelInterface => $client->channel(),
    ChannelInterface::class => static fn(Channel $channel): ChannelInterface => $channel,
    RequestResources::class => static fn(Client $client): RequestResources => new BunnyRequestResources($client),
    CommandsExchangeName::class => static fn(
        Configuration $configuration,
        ApplicationNamespace $applicationNamespace,
    ): CommandsExchangeName => new CommandsExchangeName($configuration->string(
        'METEIA_AMQ_COMMANDS_EXCHANGE_NAME',
        (string) $applicationNamespace . '.Commands',
    )),
    AmbientMessageScopeSource::class =>
        static fn(MessageScope $scope): AmbientMessageScopeSource => new AmbientMessageScopeSource($scope),
    MessageScopeSource::class => static fn(AmbientMessageScopeSource $source): MessageScopeSource => $source,
    VHostName::class => static fn(Configuration $config): VHostName => new VHostName(
        $config->string('RABBITMQ_VIRTUALHOST', '/'),
    ),
    BunnyRabbitMqManagement::class => static fn(Client $client): BunnyRabbitMqManagement => new BunnyRabbitMqManagement($client),
    RabbitMqManagement::class => IsolatedBunnyRabbitMqManagement::class,
];
