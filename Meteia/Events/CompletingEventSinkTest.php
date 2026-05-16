<?php

declare(strict_types=1);

namespace Meteia\Events;

use DateTimeImmutable;
use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\EventSourcing\Fixtures\AggregateRecorded;
use Meteia\EventSourcing\StreamId;
use Meteia\EventSourcing\StreamVersion;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\ProcessId;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CompletingEventSinkTest extends TestCase
{
    public function testCompletesUnitOfWorkWithTheMessageScopeAfterTheInnerSinkDrains(): void
    {
        $published = self::publishedEvent();
        $scope = self::scope();
        $drainedScope = null;
        $completedScope = null;

        $inner = $this->createStub(EventSink::class);
        $inner->method('drain')->willReturnCallback(
            static function (PublishedEvent $event, MessageScope $messageScope) use ($published, &$drainedScope): void {
                \assert($event === $published, 'CompletingEventSink must pass through the published event.');
                $drainedScope = $messageScope;
            },
        );
        $unitOfWork = $this->createStub(UnitOfWork::class);
        $unitOfWork->method('complete')->willReturnCallback(
            static function (MessageScope $messageScope) use (&$completedScope): void {
                $completedScope = $messageScope;
            },
        );

        (new CompletingEventSink($inner, $unitOfWork))->drain($published, $scope);

        static::assertSame($scope, $drainedScope);
        static::assertSame($scope, $completedScope);
    }

    private static function publishedEvent(): PublishedEvent
    {
        return PublishedEvent::fromMessage(
            StreamId::random(),
            StreamVersion::start(),
            new AggregateRecorded(),
            CausationId::random(),
            CorrelationId::random(),
            new DateTimeImmutable(),
        );
    }

    private static function scope(): MessageScope
    {
        return new MessageScope(
            CorrelationId::random(),
            CausationId::random(),
            ProcessId::random(),
        );
    }
}
