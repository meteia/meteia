<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Override;
use Psr\Log\AbstractLogger;
use RuntimeException;
use Socket;
use Stringable;

class UdpSystemLog extends AbstractLogger
{
    private Socket $socket;

    public function __construct(
        private readonly string $hostname = '127.0.0.1',
        private readonly int $port = 10_514,
        $facility = 23,
        private readonly Logfmt $logfmt = new Logfmt(),
    ) {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($socket === false) {
            throw new RuntimeException('Failed to create UDP socket');
        }
        $this->socket = $socket;
    }

    public function __destruct()
    {
        socket_close($this->socket);
    }

    #[Override]
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $payload = $this->logfmt->format((string) $level, (string) $message, $context) . PHP_EOL;
        socket_sendto($this->socket, $payload, \strlen($payload), 0, $this->hostname, $this->port);
    }
}
