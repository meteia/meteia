<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

use Meteia\Commands\Command;
use Meteia\Domain\CommandMessages;
use Meteia\Domain\CommandMetadata;

interface IssuedCommands
{
    public function pending(): CommandMessages;

    public function append(CommandMetadata $metadata, Command $command): void;
}
