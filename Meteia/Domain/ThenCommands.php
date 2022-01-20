<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\Domain\Contracts\Command;
use Meteia\Domain\Contracts\UnitOfWorkContext;
use Meteia\Domain\ValueObjects\ImmutableCommands;

trait ThenCommands
{
    /** @var Command[] */
    private $__pendingCommands = [];

    public function then(Command $event)
    {
        return $this->appendCommand($event);
    }

    public function commitCommandsIn(UnitOfWorkContext $unitOfWorkContext)
    {
        $unitOfWorkContext->commitCommands(new ImmutableCommands($this->__pendingCommands));

        return $this->withoutPendingCommands();
    }

    private function appendCommand(Command $event)
    {
        $copy = clone $this;
        $copy->__pendingCommands[] = $event;

        return $copy;
    }

    private function withoutPendingCommands()
    {
        $copy = clone $this;
        $copy->__pendingCommands = [];

        return $copy;
    }
}
