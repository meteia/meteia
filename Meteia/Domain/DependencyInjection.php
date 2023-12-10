<?php

declare(strict_types=1);

use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\Domain\ImmediateUnitOfWork;

return [
    UnitOfWork::class => ImmediateUnitOfWork::class,
];
