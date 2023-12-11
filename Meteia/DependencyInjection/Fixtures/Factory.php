<?php

declare(strict_types=1);

namespace Meteia\DependencyInjection\Fixtures;

class Factory
{
    public function __construct(private InnerDependency $innerDependency)
    {
    }

    public function create(string $requiredString): FactoryOutput
    {
        return new FactoryProduct($requiredString);
    }
}
