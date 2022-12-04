<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects;

use Meteia\Domain\Contracts\Command;

class Commands extends ImmutableArrayValueObject
{
    public const TYPE = Command::class;

    public function publishTo(CommandExchange $commandExchange): void
    {
        /** @var Command $command */
        foreach ($this as $command) {
            $commandExchange->publish($command);
        }
    }
}
