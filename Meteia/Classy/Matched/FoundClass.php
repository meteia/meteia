<?php

declare(strict_types=1);

namespace Meteia\Classy\Matched;

use Meteia\Classy\MatchedClass;
use Meteia\DependencyInjection\Container;

final readonly class FoundClass implements MatchedClass
{
    public function __construct(
        private string $className,
    ) {}

    #[\Override]
    public function resolveIn(Container $container): object
    {
        $instance = $container->get($this->className);
        \assert(\is_object($instance), 'container binding for matched class must produce an object');

        return $instance;
    }
}
