<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;

interface UnitOfWork extends UnitOfWorkContext
{
    public function complete(CausationId $causationId, CorrelationId $correlationId);
}
