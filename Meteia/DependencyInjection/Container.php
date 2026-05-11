<?php

declare(strict_types=1);

namespace Meteia\DependencyInjection;

use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface
{
    /**
     * @param array<array-key, mixed> $parameters
     */
    public function call($callable, array $parameters = []): mixed;

    public function set(string $id, mixed $value): void;
}
