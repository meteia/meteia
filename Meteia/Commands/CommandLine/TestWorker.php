<?php

declare(strict_types=1);

namespace Meteia\Commands\CommandLine;

use Meteia\CommandLine\Command;
use Override;
use Symfony\Component\Console\Input\InputDefinition;

readonly class TestWorker implements Command
{
    #[Override]
    public function execute(): void {}

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
