<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Psr\Log\AbstractLogger;
use Stringable;

use function strlen;

class UdpSystemLog extends AbstractLogger
{
    // private const FACILITY_MAX = 23;
    // private const FACILITY_MIN = 0;

    /**
     * @var resource
     */
    private $socket;

    public function __construct(private string $hostname = '127.0.0.1', private int $port = 10514, $facility = 23)
    {
        // Assertion::between($facility, self::FACILITY_MIN, self::FACILITY_MAX);
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    }

    public function __destruct()
    {
        socket_close($this->socket);
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $message .= PHP_EOL;
        socket_sendto($this->socket, $message, strlen($message), 0, $this->hostname, $this->port);
    }
}
