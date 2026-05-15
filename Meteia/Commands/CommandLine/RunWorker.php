<?php

declare(strict_types=1);

namespace Meteia\Commands\CommandLine;

use InvalidArgumentException;
use Meteia\Application\Command as ApplicationCommand;
use Meteia\Application\CommandBus;
use Meteia\Application\Rejected;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Bootstrap\ApplicationPublicDir;
use Meteia\CommandLine\Command as CLICommand;
use Meteia\CommandLine\PayloadParser;
use Meteia\Commands\Command;
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

final readonly class RunWorker implements CLICommand, CommandSink
{
    private stdClass $state;

    public function __construct(
        private Commands $commands,
        private LoggerInterface $log,
        private CommandInbox $commandInbox,
        private Container $container,
    ) {
        $this->state = (object) ['appContainer' => null];
    }

    #[Override]
    public static function description(): string
    {
        return 'Run the command worker queue. Supports --only <Dotted.Command> and --once for targeted single handling.';
    }

    #[Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputOption(
                'only',
                '',
                InputOption::VALUE_REQUIRED,
                'Only subscribe to the specified dotted command class, e.g. App.Commands.CreateUser',
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
        $namespace = $this->container->get(ApplicationNamespace::class);

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
            \assert(\is_string($command), 'command from Commands must be class string');
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
        if ($this->state->appContainer === null) {
            $path = $this->container->get(ApplicationPath::class);
            $namespace = $this->container->get(ApplicationNamespace::class);
            $publicDir = $this->container->get(ApplicationPublicDir::class);
            $applicationDefinitions = [
                ApplicationNamespace::class => $namespace,
                ApplicationPath::class => $path,
                ApplicationPublicDir::class => $publicDir,
            ];
            $this->state->appContainer = ContainerBuilder::build($path, $namespace, $applicationDefinitions);
        }

        return $this->state->appContainer;
    }

    #[Override]
    public function drain(Command $command, MessageScope $scope): void
    {
        try {
            \assert($command instanceof ApplicationCommand, sprintf(
                'Queued command %s must also implement %s',
                $command::class,
                ApplicationCommand::class,
            ));
            $bus = $this->appContainer()->get(CommandBus::class);
            \assert($bus instanceof CommandBus, 'CommandBus must be resolvable from app container');
            $result = $bus->dispatch($command);
            if ($result instanceof Rejected) {
                $this->log->error('Command rejected', [
                    'command' => $command::class,
                    'reason' => $result->reason(),
                ]);
            }
        } catch (Throwable $e) {
            $this->log->error('Command failed', ['exception' => $e]);
        }

        gc_collect_cycles();
    }
}
