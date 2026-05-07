<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\ValueObjects\Identity\MessageScope;

interface CommandSink
{
    public function drain(Command $command, MessageScope $scope): void;
}
