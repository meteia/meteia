<?php

declare(strict_types=1);

namespace Meteia\Commands;

interface DueDelayedCommands
{
    public function dispatch(int $limit): int;
}
