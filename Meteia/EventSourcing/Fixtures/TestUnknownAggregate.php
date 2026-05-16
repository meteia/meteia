<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Fixtures;

use Meteia\EventSourcing\Contracts\UnknownAggregate;
use Meteia\ValueObjects\AggregateRootId;
use Override;
use RuntimeException;
use Throwable;

/**
 * @internal
 *
 * @implements UnknownAggregate<TestAggregateId>
 */
final readonly class TestUnknownAggregate implements UnknownAggregate
{
    #[Override]
    public function error(AggregateRootId $id): Throwable
    {
        return new RuntimeException('Unknown aggregate.');
    }
}
