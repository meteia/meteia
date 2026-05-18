<?php

declare(strict_types=1);

namespace Meteia\PsrConvention\CommandHandlers;

use Meteia\Commands\Command;
use Meteia\Commands\CommandHandler;
use Meteia\PsrConvention\Commands\ExamplePsrCommand as CommandFixture;
use Override;

/**
 * @implements CommandHandler<CommandFixture, void>
 */
final class ExamplePsrCommand implements CommandHandler
{
    public bool $handled = false;

    #[Override]
    public function handle(Command $command): void
    {
        $this->handled = true;
    }
}
