<?php

declare(strict_types=1);

namespace Meteia\Commands;

interface CommandHandlers
{
    public function handlerFor(Command $command): CommandHandler;
}
