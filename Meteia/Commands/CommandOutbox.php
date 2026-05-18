<?php

declare(strict_types=1);

namespace Meteia\Commands;

interface CommandOutbox
{
    /**
     * @param Command<mixed> $command
     */
    public function publish(Command $command): void;
}
