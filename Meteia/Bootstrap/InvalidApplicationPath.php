<?php

declare(strict_types=1);

namespace Meteia\Bootstrap;

use DomainException;

final class InvalidApplicationPath extends DomainException
{
    public function __construct(string $path)
    {
        parent::__construct("Invalid ApplicationPath: {$path}");
    }
}
