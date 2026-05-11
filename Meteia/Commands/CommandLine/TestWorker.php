<?php

declare(strict_types=1);

namespace Meteia\Commands\CommandLine;

use Meteia\CommandLine\Command;
use Meteia\Commands\CommandOutbox;
use Meteia\Files\Commands\DeleteFile;
use Override;
use Symfony\Component\Console\Input\InputDefinition;

readonly class TestWorker implements Command
{
    public function __construct(
        private CommandOutbox $commandOutbox,
    ) {}

    #[Override]
    public function execute(): void
    {
        //        while (true) {
        $this->commandOutbox->publish(new DeleteFile('/dev/null'));

        //        }
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
