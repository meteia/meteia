<?php

declare(strict_types=1);

namespace Meteia\Classy;

use Meteia\DependencyInjection\Container;

interface MatchedClass
{
    public function resolveIn(Container $container): object;
}
