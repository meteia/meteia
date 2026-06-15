<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Override;
use Psr\Log\AbstractLogger;
use RuntimeException;
use Socket;
use Stringable;

class TcpSystemLog extends AbstractLogger
{
    private ?Socket $socket = null;

    public function __construct(
        private readonly string $hostname = '127.0.0.1',
        private readonly int $port = 10_514,
    ) {}

    public function __destruct()
    {
        if ($this->socket !== null) {
            socket_close($this->socket);
        }
    }

    #[Override]
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $payload = (string) $message . PHP_EOL;
        socket_write($this->connectedSocket(), $payload, \strlen($payload));
    }

    private function connectedSocket(): Socket
    {
        if ($this->socket !== null) {
            return $this->socket;
        }

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            throw new RuntimeException('Failed to create TCP socket');
        }
        if (!socket_connect($socket, $this->hostname, $this->port)) {
            socket_close($socket);

            throw new RuntimeException('Failed to connect TCP socket');
        }

        return $this->socket = $socket;
    }
}