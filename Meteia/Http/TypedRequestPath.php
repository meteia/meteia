<?php

declare(strict_types=1);

namespace Meteia\Http;

use Meteia\Configuration\BooleanValues;
use Psr\Http\Message\ServerRequestInterface;

class TypedRequestPath
{
    /** @var array<int, string> */
    private array $path;

    public function __construct(
        private readonly BooleanValues $booleanValues,
        ServerRequestInterface $sri,
    ) {
        $this->path = array_values(array_filter(explode('/', $sri->getUri()->getPath())));
    }

    public function string(int $idx, string $default): string
    {
        return $this->path[$idx] ?? $default;
    }

    public function int(int $idx, int $default): int
    {
        return (int) ($this->path[$idx] ?? $default);
    }

    public function boolean(int $idx, bool $default): bool
    {
        return $this->booleanValues->boolean($this->path[$idx] ?? $default);
    }

    public function float(int $idx, float $default): float
    {
        return (float) ($this->path[$idx] ?? $default);
    }
}
