<?php

declare(strict_types=1);

namespace Meteia\Commands;

interface CommandBus
{
    public function dispatch(Command $command): CommandResult;
}
