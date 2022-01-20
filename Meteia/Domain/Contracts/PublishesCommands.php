<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

interface PublishesCommands
{
    public function publish(Command $command);
}
