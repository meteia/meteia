<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Fixtures;

use Meteia\ValueObjects\AggregateRootId;
use Override;

/**
 * @internal
 */
final readonly class TestAggregateId extends AggregateRootId
{
    #[Override]
    public static function prefix(): string
    {
        return 'tag';
    }
}
