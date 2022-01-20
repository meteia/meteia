<?php

declare(strict_types=1);

namespace Meteia\DependencyInjection\Fixtures;

class Definitions
{
    public static function get(): array
    {
        return [
            FactoryOutput::class => fn (Factory $factory) => $factory->create('something'),
        ];
    }
}
