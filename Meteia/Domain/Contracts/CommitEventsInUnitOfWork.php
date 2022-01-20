<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

interface CommitEventsInUnitOfWork
{
    public function commitInto(UnitOfWorkContext $unitOfWorkContext);
}
