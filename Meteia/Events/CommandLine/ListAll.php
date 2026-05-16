<?php

declare(strict_types=1);

namespace Meteia\Events\CommandLine;

use Meteia\CommandLine\Command;
use Meteia\Events\Events as DomainEvents;
use Override;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class ListAll implements Command
{
    public function __construct(
        private InputInterface $input,
        private OutputInterface $output,
        private DomainEvents $events,
    ) {}

    #[Override]
    public static function description(): string
    {
        return 'List all domain events available for events:send.';
    }

    #[Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition();
    }

    #[Override]
    public function execute(): void
    {
        $dotted = [];
        foreach ($this->events as $class) {
            $dotted[] = str_replace('\\', '.', $class);
        }
        $dotted = array_values(array_unique($dotted));
        sort($dotted);

        $this->output->writeln('<info>Domain Events:</info>');
        foreach ($dotted as $name) {
            $this->output->writeln('  ' . $name);
        }
        $this->output->writeln(sprintf('<comment>%d total</comment>', \count($dotted)));
    }
}
