<?php

declare(strict_types=1);

namespace Meteia\Projections\Contracts;

use Meteia\Projections\ProjectionName;

interface CheckpointStore
{
    public function load(ProjectionName $name): Checkpoint;

    public function save(ProjectionName $name, Checkpoint $checkpoint): void;
}
