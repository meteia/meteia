<?php

declare(strict_types=1);

namespace Meteia\DependencyInjection\Fixtures;

class FactoryProduct implements FactoryOutput
{
    public function __construct(private string $requiredString)
    {
    }
}
