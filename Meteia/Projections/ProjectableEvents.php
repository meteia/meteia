<?php

declare(strict_types=1);

namespace Meteia\Projections;

use Meteia\ValueObjects\ImmutableArrayValueObject;

final readonly class ProjectableEvents extends ImmutableArrayValueObject
{
    public const string TYPE = ProjectableEvent::class;
}
