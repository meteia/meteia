<?php

declare(strict_types=1);

namespace Meteia\Projections\Contracts;

use Meteia\EventSourcing\RecordedEvent;
use Meteia\Projections\ProjectionName;

interface Projection
{
    public function name(): ProjectionName;

    public function project(RecordedEvent $event): void;
}
