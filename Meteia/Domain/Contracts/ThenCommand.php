<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

interface ThenCommand
{
    public function then(Command $command);
}
