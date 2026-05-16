<?php

declare(strict_types=1);

namespace Meteia\Commands\CommandLine;

use InvalidArgumentException;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Bootstrap\ApplicationPublicDir;
use Meteia\CommandLine\Command as CLICommand;
use Meteia\CommandLine\PayloadParser;
use Meteia\Commands\Command;
use Meteia\Commands\CommandBus;
use Meteia\Commands\CommandInbox;
use Meteia\Commands\Commands;
use Meteia\Commands\CommandSink;
use Meteia\DependencyInjection\Container;
use Meteia\DependencyInjection\ContainerBuilder;
use Meteia\ValueObjects\Identity\MessageScope;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

final class RunWorker implements CLICommand, CommandSink
{
    private ?Container $appContainer;

    public function __construct(
        private Commands $commands,
        private LoggerInterface $log,
        private CommandInbox $commandInbox,
        private Container $container,
    ) {
        $this->appContainer = null;
    }

    #[Override]
    public static function description(): string
    {
        return 'Run the command worker queue.';
    }

    #[Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputOption(
                'only',
                '',
                InputOption::VALUE_REQUIRED,
                'Only subscribe to the specified dotted command class, e.g. App.Users.Commands.CreateUser',
            ),
            new InputOption(
                'once',
                '',
                InputOption::VALUE_NONE,
                'Exit after handling one command (typically used with --only)',
            ),
        ]);
    }

    #[Override]
    public function execute(): void
    {
        $input = $this->container->get(InputInterface::class);
        \assert($input instanceof InputInterface, 'console input must be available in the command worker container');
        $namespace = $this->container->get(ApplicationNamespace::class);
        \assert($namespace instanceof ApplicationNamespace, 'application namespace must be available in the command worker container');

        $only = $input->getOption('only');
        $once = (bool) $input->getOption('once');

        $targetCommand = null;
        if ($only !== null) {
            $target = (string) $only;
            $parser = new PayloadParser();
            $targetCommand = $parser->resolve($target, $namespace, Command::class);
            if ($targetCommand === null) {
                throw new InvalidArgumentException(sprintf(
                    'Target "%s" must resolve to a class implementing %s',
                    $target,
                    Command::class,
                ));
            }
        }

        foreach ($this->commands as $command) {
            if ($targetCommand !== null && $command !== $targetCommand) {
                continue;
            }
            $suffix = $targetCommand !== null ? ' (only)' : '';
            $this->log->info('Registering command sink' . $suffix, ['command' => $command]);
            $this->commandInbox->subscribe($command, $this);
        }

        $this->log->info('Running command worker' . ($once ? ' (once)' : ''));
        if ($once) {
            $this->commandInbox->runOnce();

            return;
        }
        $this->commandInbox->run();
    }

    private function appContainer(): Container
    {
        if ($this->appContainer === null) {
            $path = $this->container->get(ApplicationPath::class);
            \assert($path instanceof ApplicationPath, 'application path must be available in the command worker container');
            $namespace = $this->container->get(ApplicationNamespace::class);
            \assert($namespace instanceof ApplicationNamespace, 'application namespace must be available in the command worker container');
            $publicDir = $this->container->get(ApplicationPublicDir::class);
            \assert($publicDir instanceof ApplicationPublicDir, 'application public dir must be available in the command worker container');
            $applicationDefinitions = [
                ApplicationNamespace::class => $namespace,
                ApplicationPath::class => $path,
                ApplicationPublicDir::class => $publicDir,
            ];
            $this->appContainer = ContainerBuilder::build($path, $namespace, $applicationDefinitions);
        }

        return $this->appContainer;
    }

    #[Override]
    public function drain(Command $command, MessageScope $scope): void
    {
        try {
            $bus = $this->appContainer()->get(CommandBus::class);
            \assert($bus instanceof CommandBus, 'CommandBus must be resolvable from app container');
            $bus->dispatch($command);
        } catch (Throwable $e) {
            $this->log->error('Command failed', ['exception' => $e]);
        }

        gc_collect_cycles();
    }
}
