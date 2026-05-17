<?php

declare(strict_types=1);

namespace Meteia\TypeScript\CommandLine;

use InvalidArgumentException;
use Meteia\CommandLine\Command;
use Meteia\Commands\Commands;
use Meteia\Events\Events;
use Meteia\TypeScript\TypeScriptDeclarations;
use Meteia\ValueObjects\Identity\FilesystemPath;
use Override;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class GenerateTypes implements Command
{
    public function __construct(
        private InputInterface $input,
        private OutputInterface $output,
        private Commands $commands,
        private Events $events,
    ) {}

    #[Override]
    public static function description(): string
    {
        return 'Generate TypeScript payload types for domain commands and events.';
    }

    #[Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputOption('output', null, InputOption::VALUE_REQUIRED, 'Write generated declarations to this file.'),
        ]);
    }

    #[Override]
    public function execute(): void
    {
        $content = new TypeScriptDeclarations($this->commands, $this->events)->content();
        $outputPath = $this->outputPath();
        if ($outputPath === null) {
            $this->output->write($content);

            return;
        }

        new FilesystemPath($outputPath)->write($content);
    }

    private function outputPath(): ?string
    {
        // @mago-expect analysis:mixed-assignment -- Symfony input options are untyped at the CLI boundary.
        $output = $this->input->getOption('output');
        if ($output === null || is_string($output)) {
            return $output;
        }

        throw new InvalidArgumentException('TypeScript:GenerateTypes option --output must be a string.');
    }
}
