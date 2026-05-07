<?php

declare(strict_types=1);

namespace Meteia\Projections;

final readonly class ProjectionName
{
    public function __construct(
        private string $value,
    ) {
        \assert($value !== '', 'ProjectionName must be non-empty');
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
