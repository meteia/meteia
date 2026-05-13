<?php

declare(strict_types=1);

use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\Domain\DeferredUnitOfWork;

return [
    UnitOfWork::class => DeferredUnitOfWork::class,
];
