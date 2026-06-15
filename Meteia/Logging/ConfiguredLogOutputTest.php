<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Meteia\Configuration\EnvironmentConfiguration;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class ConfiguredLogOutputTest extends TestCase
{
    #[Override]
    protected function tearDown(): void
    {
        unset($_ENV['SYSLOG_HOST'], $_ENV['SYSLOG_PORT_UDP'], $_ENV['SYSLOG_PORT_TCP'], $_ENV['VICTORIALOGS_URL']);

        parent::tearDown();
    }

    public function testInteractiveShellUsesStandardError(): void
    {
        $_ENV['SYSLOG_PORT_UDP'] = '10514';

        $output = new ConfiguredLogOutput(new EnvironmentConfiguration(), true)->create();

        static::assertInstanceOf(StandardError::class, $output);
    }

    public function testUdpPortUsesRfc5424FormattedSyslog(): void
    {
        $_ENV['SYSLOG_PORT_UDP'] = '10514';

        $output = new ConfiguredLogOutput(new EnvironmentConfiguration(), false)->create();

        static::assertInstanceOf(RFC5424Formatted::class, $output);
    }

    public function testVictoriaLogsUrlDerivesSyslogHostWhenUnset(): void
    {
        $_ENV['VICTORIALOGS_URL'] = 'http://10.0.0.9:9428';
        $_ENV['SYSLOG_PORT_UDP'] = '10514';

        $output = new ConfiguredLogOutput(new EnvironmentConfiguration(), false)->create();
        static::assertInstanceOf(RFC5424Formatted::class, $output);

        $inner = new \ReflectionClass($output)->getProperty('log');
        $udp = $inner->getValue($output);
        static::assertInstanceOf(UdpSystemLog::class, $udp);

        $host = new \ReflectionClass($udp)->getProperty('hostname');
        static::assertSame('10.0.0.9', $host->getValue($udp));
    }

    public function testSyslogHostIsPassedToUdpSystemLog(): void
    {
        $_ENV['SYSLOG_HOST'] = '10.0.0.5';
        $_ENV['SYSLOG_PORT_UDP'] = '10514';

        $output = new ConfiguredLogOutput(new EnvironmentConfiguration(), false)->create();
        static::assertInstanceOf(RFC5424Formatted::class, $output);

        $inner = new \ReflectionClass($output)->getProperty('log');
        $udp = $inner->getValue($output);
        static::assertInstanceOf(UdpSystemLog::class, $udp);

        $host = new \ReflectionClass($udp)->getProperty('hostname');
        static::assertSame('10.0.0.5', $host->getValue($udp));
    }

    public function testTcpPortUsesRfc5424FormattedSyslog(): void
    {
        $_ENV['SYSLOG_PORT_TCP'] = '10514';

        $output = new ConfiguredLogOutput(new EnvironmentConfiguration(), false)->create();

        static::assertInstanceOf(RFC5424Formatted::class, $output);
    }

    public function testUdpPortTakesPrecedenceOverTcpPort(): void
    {
        $_ENV['SYSLOG_PORT_UDP'] = '10514';
        $_ENV['SYSLOG_PORT_TCP'] = '10515';

        $output = new ConfiguredLogOutput(new EnvironmentConfiguration(), false)->create();

        static::assertInstanceOf(RFC5424Formatted::class, $output);
    }

    public function testNoSyslogPortsUsesStandardError(): void
    {
        $output = new ConfiguredLogOutput(new EnvironmentConfiguration(), false)->create();

        static::assertInstanceOf(StandardError::class, $output);
    }

    public function testCreateReturnsLoggerInterface(): void
    {
        $output = new ConfiguredLogOutput(new EnvironmentConfiguration(), false)->create();

        static::assertInstanceOf(LoggerInterface::class, $output);
    }
}