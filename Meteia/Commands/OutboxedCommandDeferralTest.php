<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Meteia\ValueObjects\Identity\ProcessId;
use Override;
use PHPUnit\Framework\TestCase;

final class OutboxedCommandDeferralTest extends TestCase
{
    public function testDeferPublishesDeliveryAndReturnsReceipt(): void
    {
        $command = new Fixtures\ExampleCommand();
        $scope = self::scope();
        $published = null;
        $deferral = new OutboxedCommandDeferral(
            new class($published) implements CommandDeliveries {
                public function __construct(
                    private ?CommandDelivery &$published,
                ) {}

                #[Override]
                public function publishDelivery(CommandDelivery $delivery): void
                {
                    $this->published = $delivery;
                }
            },
            self::scopeSource($scope),
        );

        $deferred = $deferral->defer($command);

        static::assertInstanceOf(CommandDelivery::class, $published);
        static::assertSame($command, $published->command());
        static::assertSame($scope, $published->scope());
        static::assertSame((string) $published->commandId(), (string) $deferred->commandId());
    }

    private static function scopeSource(MessageScope $scope): MessageScopeSource
    {
        return new readonly class($scope) implements MessageScopeSource {
            public function __construct(
                private MessageScope $scope,
            ) {}

            #[Override]
            public function current(): MessageScope
            {
                return $this->scope;
            }
        };
    }

    private static function scope(): MessageScope
    {
        return new MessageScope(CorrelationId::random(), CausationId::random(), ProcessId::random());
    }
}
