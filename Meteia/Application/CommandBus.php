<?php

declare(strict_types=1);

namespace Meteia\Application;

interface CommandBus
{
    public function dispatch(Command $command): CommandResult;
}
