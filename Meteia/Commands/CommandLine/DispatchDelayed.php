<?php

declare(strict_types=1);

namespace Meteia\Commands\CommandLine;

use Meteia\CommandLine\Command;
use Meteia\Commands\DueDelayedCommands;
use Override;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class DispatchDelayed implements Command
{
    public function __construct(
        private InputInterface $input,
        private OutputInterface $output,
        private DueDelayedCommands $commands,
    ) {}

    #[Override]
    public static function description(): string
    {
        return 'Publish due delayed commands to the command queue.';
    }

    #[Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputOption('limit', '', InputOption::VALUE_REQUIRED, 'Maximum delayed commands to dispatch', 100),
        ]);
    }

    #[Override]
    public function execute(): void
    {
        $limit = (int) $this->input->getOption('limit');
        $dispatched = $this->commands->dispatch($limit);

        $this->output->writeln(sprintf('Dispatched %d delayed command(s).', $dispatched));
    }
}
