<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Contracts;

use Meteia\Projections\GlobalSequence;
use Meteia\Projections\ProjectableEvents;

interface GlobalEventStream
{
    /**
     * Read recorded events across every stream, ordered by global insertion sequence,
     * starting strictly after the given position.
     */
    public function readGlobally(GlobalSequence $after = new GlobalSequence(0)): ProjectableEvents;
}
