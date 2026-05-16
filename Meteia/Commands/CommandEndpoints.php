<?php

declare(strict_types=1);

namespace Meteia\Commands;

interface CommandEndpoints
{
    /**
     * @param class-string<Command> $commandClass
     */
    public function endpointFor(string $commandClass): CommandEndpoint;
}
