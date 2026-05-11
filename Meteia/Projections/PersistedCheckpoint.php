<?php

declare(strict_types=1);

namespace Meteia\Projections;

use InvalidArgumentException;
use Meteia\Projections\Contracts\Checkpoint;
use NoDiscard;
use Override;

final readonly class PersistedCheckpoint implements Checkpoint
{
    public function __construct(
        private GlobalSequence $position,
    ) {}

    #[Override]
    public function position(): GlobalSequence
    {
        return $this->position;
    }

    #[Override]
    #[NoDiscard]
    public function advancedTo(GlobalSequence $next): self
    {
        if (!$next->isGreaterThan($this->position) && !$next->equalTo($this->position)) {
            throw new InvalidArgumentException('Checkpoint may only advance forward.');
        }

        return new self($next);
    }
}
