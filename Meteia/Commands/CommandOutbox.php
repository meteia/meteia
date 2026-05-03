<?php

declare(strict_types=1);

namespace Meteia\Commands;

interface CommandOutbox
{
    public function publish(Command $command): void;
}
