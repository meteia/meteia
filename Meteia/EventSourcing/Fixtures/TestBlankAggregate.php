<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Fixtures;

use Meteia\EventSourcing\Contracts\BlankAggregate;
use Meteia\EventSourcing\Contracts\EventSourced;
use Meteia\ValueObjects\AggregateRootId;
use Override;

/**
 * @internal
 *
 * @implements BlankAggregate<TestAggregateId, TestEventSourcedAggregate>
 */
final readonly class TestBlankAggregate implements BlankAggregate
{
    #[Override]
    public function of(AggregateRootId $id): EventSourced
    {
        return new TestEventSourcedAggregate($id);
    }
}
