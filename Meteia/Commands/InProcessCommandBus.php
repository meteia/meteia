<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Override;

final readonly class InProcessCommandBus implements CommandBus
{
    public function __construct(
        private CommandHandlers $handlers,
    ) {}

    #[Override]
    public function dispatch(Command $command): void
    {
        $this->handlers->handlerFor($command)->handle($command);
    }
}
