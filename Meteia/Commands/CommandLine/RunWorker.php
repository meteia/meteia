<?php

declare(strict_types=1);

namespace Meteia\Commands\CommandLine;

use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Bootstrap\ApplicationPublicDir;
use Meteia\CommandLine\Command as CLICommand;
use Meteia\Commands\Command;
use Meteia\Commands\CommandInbox;
use Meteia\Commands\Commands;
use Meteia\Commands\CommandSink;
use Meteia\DependencyInjection\Container;
use Meteia\DependencyInjection\ContainerBuilder;
use Meteia\ValueObjects\Identity\MessageScope;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputDefinition;

readonly class RunWorker implements CLICommand, CommandSink
{
    private Container $container;

    public function __construct(
        private Commands $commands,
        private LoggerInterface $log,
        private ApplicationPath $path,
        private CommandInbox $commandInbox,
        private ApplicationNamespace $namespace,
        private ApplicationPublicDir $publicDir,
    ) {
        $applicationDefinitions = [
            ApplicationNamespace::class => $this->namespace,
            ApplicationPath::class => $this->path,
            ApplicationPublicDir::class => $this->publicDir,
        ];
        $this->container = ContainerBuilder::build($this->path, $this->namespace, $applicationDefinitions);
    }

    #[\Override]
    public static function description(): string
    {
        return 'Run the command worker queue';
    }

    #[\Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition();
    }

    #[\Override]
    public function execute(): void
    {
        foreach ($this->commands as $command) {
            $this->log->info('Registering command sink', ['command' => $command]);
            $this->commandInbox->subscribe($command, $this);
        }
        $this->log->info('Running command worker');
        $this->commandInbox->run();
    }

    #[\Override]
    public function drain(Command $command, MessageScope $scope): void
    {
        try {
            \assert(method_exists($command, 'invoke'));
            $this->container->call($command->invoke(...));
        } catch (\Throwable $e) {
            $this->log->error('Command failed', ['exception' => $e]);
        }

        gc_collect_cycles();
    }
}
