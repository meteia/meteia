<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

use Meteia\Commands\Command;
use Meteia\Domain\CommandMetadata;

interface CommandMessageTarget
{
    public function handleCommandMessage(Command $command, CommandMetadata $metadata);
}
