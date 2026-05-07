<?php

declare(strict_types=1);

namespace Meteia\Projections\Contracts;

use Meteia\Projections\GlobalSequence;

interface Checkpoint
{
    public function position(): GlobalSequence;

    #[\NoDiscard]
    public function advancedTo(GlobalSequence $next): self;
}
