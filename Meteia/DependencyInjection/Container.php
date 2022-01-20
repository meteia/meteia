<?php

declare(strict_types=1);

namespace Meteia\DependencyInjection;

use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface
{
    public function call($callable, array $parameters = []): mixed;
}
