<?php

declare(strict_types=1);

namespace Meteia\Application;

final readonly class InProcessCommandBus implements CommandBus
{
    public function __construct(
        private CommandEndpointRegistry $endpoints,
    ) {}

    #[\Override]
    public function dispatch(Command $command): CommandResult
    {
        return $this->endpoints->endpointFor($command::class)->handle($command);
    }
}
