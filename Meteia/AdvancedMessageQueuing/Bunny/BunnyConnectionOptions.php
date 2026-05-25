<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Client;
use Meteia\Configuration\Configuration;

final readonly class BunnyConnectionOptions
{
    public function __construct(
        private Configuration $configuration,
    ) {}

    public function client(): Client
    {
        return new Client($this->values());
    }

    /**
     * @return array{
     *     host: string,
     *     port: int,
     *     vhost: string,
     *     user: string,
     *     password: string,
     *     timeout: int,
     *     heartbeat: float,
     *     keepAlive: bool,
     * }
     */
    public function values(): array
    {
        return [
            'host' => $this->configuration->string('RABBITMQ_HOST', '127.0.0.1'),
            'port' => $this->configuration->int('RABBITMQ_PORT', 5672),
            'vhost' => $this->configuration->string('RABBITMQ_VIRTUALHOST', '/'),
            'user' => $this->configuration->string('RABBITMQ_USERNAME', 'guest'),
            'password' => $this->configuration->string('RABBITMQ_PASSWORD', 'guest'),
            'timeout' => $this->configuration->int('RABBITMQ_TIMEOUT', 1),
            'heartbeat' => $this->configuration->float('RABBITMQ_HEARTBEAT', 60.0),
            'keepAlive' => $this->configuration->boolean('RABBITMQ_KEEPALIVE', false),
        ];
    }
}
