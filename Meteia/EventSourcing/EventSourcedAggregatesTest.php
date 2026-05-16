<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\EventSourcing\Fixtures\CapturingUnitOfWork;
use Meteia\EventSourcing\Fixtures\EmptyEventStream;
use Meteia\EventSourcing\Fixtures\ReplayingEventStream;
use Meteia\EventSourcing\Fixtures\TestAggregateId;
use Meteia\EventSourcing\Fixtures\TestBlankAggregate;
use Meteia\EventSourcing\Fixtures\TestEventSourcedAggregate;
use Meteia\EventSourcing\Fixtures\TestUnknownAggregate;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
final class EventSourcedAggregatesTest extends TestCase
{
    public function testReconstitutedReturnsReplayFilledAggregate(): void
    {
        $id = TestAggregateId::random();
        $aggregate = new EventSourcedAggregates(
            new ReplayingEventStream(),
            new CapturingUnitOfWork(),
            new TestBlankAggregate(),
            new TestUnknownAggregate(),
        )->reconstituted($id);

        EventSourcedAggregatesTest::assertInstanceOf(TestEventSourcedAggregate::class, $aggregate);
        EventSourcedAggregatesTest::assertSame(0, $aggregate->observedSequence());
    }

    public function testReconstitutedThrowsMissingAggregate(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown aggregate.');

        new EventSourcedAggregates(
            new EmptyEventStream(),
            new CapturingUnitOfWork(),
            new TestBlankAggregate(),
            new TestUnknownAggregate(),
        )->reconstituted(TestAggregateId::random());
    }

    public function testCommitHandsPendingEventsToUnitOfWork(): void
    {
        $unitOfWork = new CapturingUnitOfWork();
        $aggregate = new TestEventSourcedAggregate(TestAggregateId::random());

        new EventSourcedAggregates(
            new EmptyEventStream(),
            $unitOfWork,
            new TestBlankAggregate(),
            new TestUnknownAggregate(),
        )->commit($aggregate);

        EventSourcedAggregatesTest::assertSame([Fixtures\AggregateRecorded::class], $unitOfWork->eventClasses());
    }
}
