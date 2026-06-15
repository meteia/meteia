<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Meteia\Configuration\Configuration;
use Psr\Log\LoggerInterface;

final readonly class ConfiguredLogOutput
{
    public function __construct(
        private Configuration $configuration,
        private bool $interactive,
    ) {}

    public static function fromEnvironment(Configuration $configuration): self
    {
        return new self($configuration, (new InteractiveShell())->isInteractive());
    }

    public function create(): LoggerInterface
    {
        if ($this->interactive) {
            return new StandardError();
        }

        $host = $this->syslogHost();

        $udpPort = $this->configuration->int('SYSLOG_PORT_UDP', 0);
        if ($udpPort > 0) {
            return new RFC5424Formatted(new UdpSystemLog(hostname: $host, port: $udpPort));
        }

        $tcpPort = $this->configuration->int('SYSLOG_PORT_TCP', 0);
        if ($tcpPort > 0) {
            return new RFC5424Formatted(new TcpSystemLog(hostname: $host, port: $tcpPort));
        }

        return new StandardError();
    }

    private function syslogHost(): string
    {
        $host = $this->configuration->string('SYSLOG_HOST', '127.0.0.1');
        if ($host !== '127.0.0.1') {
            return $host;
        }

        $victoriaLogsUrl = $this->configuration->string('VICTORIALOGS_URL', '');
        if ($victoriaLogsUrl === '') {
            return $host;
        }

        $parts = parse_url($victoriaLogsUrl);
        if (!\is_array($parts)) {
            return $host;
        }

        $derivedHost = $parts['host'] ?? null;

        return \is_string($derivedHost) ? $derivedHost : $host;
    }
}