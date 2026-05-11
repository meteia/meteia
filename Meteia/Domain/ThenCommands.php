<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\Commands\Command;
use Meteia\Domain\Contracts\UnitOfWorkContext;

trait ThenCommands
{
    /** @var list<Command> */
    private array $__pendingCommands = [];

    public function then(Command $event): self
    {
        return $this->appendCommand($event);
    }

    public function commitCommandsIn(UnitOfWorkContext $unitOfWorkContext): self
    {
        $unitOfWorkContext->wantsTo(new PendingCommands($this->__pendingCommands));

        return $this->withoutPendingCommands();
    }

    private function appendCommand(Command $event): self
    {
        $copy = clone $this;
        $copy->__pendingCommands[] = $event;

        return $copy;
    }

    private function withoutPendingCommands(): self
    {
        $copy = clone $this;
        $copy->__pendingCommands = [];

        return $copy;
    }
}
