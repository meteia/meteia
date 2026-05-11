<?php

declare(strict_types=1);

namespace Meteia\Classy;

use Override;
use Stringable;

final readonly class ClassBasedName implements Stringable
{
    private const array OMITTED_SEGMENTS = [
        'ApiServer',
        'GraphQL',
        'Types',
        'Queries',
        'Mutations',
    ];

    public function __construct(
        private string $class,
    ) {}

    #[Override]
    public function __toString(): string
    {
        $segments = explode('\\', $this->class);
        array_shift($segments);

        return implode('_', array_diff($segments, self::OMITTED_SEGMENTS));
    }
}
