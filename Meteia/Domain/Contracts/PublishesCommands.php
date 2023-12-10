<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

use Meteia\Commands\Command;

interface PublishesCommands
{
    public function publish(Command $command);
}
