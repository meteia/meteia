<?php

declare(strict_types=1);

namespace Meteia\Commands\CommandLine;

use Meteia\CommandLine\Command;
use Meteia\Commands\CommandBus;
use Meteia\Files\Commands\DeleteFile;
use Override;
use Symfony\Component\Console\Input\InputDefinition;

readonly class TestWorker implements Command
{
    public function __construct(
        private CommandBus $commandBus,
    ) {
    }

    #[Override]
    public function execute(): void
    {
        while (true) {
            $this->commandBus->publishCommand(
                new DeleteFile('/dev/null'),
            );
        }
    }

    #[Override]
    public static function description(): string
    {
        return 'Push test events and commands for the worker queue to pick up.';
    }

    #[Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition();
    }
}
