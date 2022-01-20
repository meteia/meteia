<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

interface CommitCommandsInUnitOfWork
{
    public function commitCommandsIn(UnitOfWorkContext $unitOfWorkContext);
}
