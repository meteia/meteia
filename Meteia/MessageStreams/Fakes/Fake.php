<?php

declare(strict_types=1);

namespace Meteia\MessageStreams\Fakes;

use Meteia\EventSourcing\EventSourcing;
use Meteia\ValueObjects\AggregateRootId;

class Fake
{
    use EventSourcing;

    public function __construct(
        private AggregateRootId $id,
    ) {}

    public function create(): void
    {
        $this->causes(new FakeOccurred('one', 'two', 'three'));
    }
}
