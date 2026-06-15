<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class RFC5424FormattedTest extends TestCase
{
    public function testFormatsSyslogMessage(): void
    {
        $lastMessage = '';
        $captured = $this->createMock(LoggerInterface::class);
        $captured->method('log')->willReturnCallback(
            static function (mixed $_level, mixed $message) use (&$lastMessage): void {
                $lastMessage = (string) $message;
            },
        );

        $logger = new RFC5424Formatted($captured);
        $logger->info('hello', ['key' => 'value']);

        static::assertMatchesRegularExpression(
            '/^<\d+>1 \S+ \S+ appName \S+ subsystem \[/',
            $lastMessage,
        );
        static::assertStringContainsString('[psr.log.context@17589 key="value"]', $lastMessage);
        static::assertStringContainsString('hello', $lastMessage);
    }

    public function testFormatsStringableContextValues(): void
    {
        $lastMessage = '';
        $captured = $this->createMock(LoggerInterface::class);
        $captured->method('log')->willReturnCallback(
            static function (mixed $_level, mixed $message) use (&$lastMessage): void {
                $lastMessage = (string) $message;
            },
        );

        $correlationId = CorrelationId::random();
        $causationId = CausationId::random();

        $logger = new RFC5424Formatted($captured);
        $logger->info('hello', [
            'correlation_id' => $correlationId,
            'causation_id' => $causationId,
        ]);

        static::assertStringContainsString(
            sprintf('correlation_id="%s"', (string) $correlationId),
            $lastMessage,
        );
        static::assertStringContainsString(
            sprintf('causation_id="%s"', (string) $causationId),
            $lastMessage,
        );
        static::assertStringNotContainsString('!object!', $lastMessage);
    }
}