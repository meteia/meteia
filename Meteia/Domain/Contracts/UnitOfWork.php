<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

use Meteia\ValueObjects\Identity\MessageScope;

interface UnitOfWork extends UnitOfWorkContext
{
    public function complete(MessageScope $scope): void;
}
