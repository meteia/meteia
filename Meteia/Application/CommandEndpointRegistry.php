<?php

declare(strict_types=1);

namespace Meteia\Application;

interface CommandEndpointRegistry
{
    /**
     * @param class-string<Command> $commandClass
     */
    public function endpointFor(string $commandClass): CommandEndpoint;
}
