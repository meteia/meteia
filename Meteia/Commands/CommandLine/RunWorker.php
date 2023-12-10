<?php

declare(strict_types=1);

namespace Meteia\Commands\CommandLine;

use Meteia\Application\ApplicationNamespace;
use Meteia\Application\ApplicationPath;
use Meteia\Application\ApplicationPublicDir;
use Meteia\CommandLine\Command as CLICommand;
use Meteia\Commands\Command;
use Meteia\Commands\CommandBus;
use Meteia\Commands\CommandId;
use Meteia\Commands\CommandMessageHandler;
use Meteia\Commands\Commands;
use Meteia\DependencyInjection\Container;
use Meteia\DependencyInjection\ContainerBuilder;
use Meteia\Events\EventBus;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\ProcessId;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Throwable;

readonly class RunWorker implements CLICommand, CommandMessageHandler
{
    private Container $container;

    public function __construct(
        private Commands $commands,
        private EventBus $eventBus,
        private LoggerInterface $log,
        private ApplicationPath $path,
        private CommandBus $commandBus,
        private ApplicationNamespace $namespace,
        private ApplicationPublicDir $publicDir,
    ) {
        $applicationDefinitions = [
            ApplicationNamespace::class => $this->namespace,
            ApplicationPath::class => $this->path,
            ApplicationPublicDir::class => $this->publicDir,
            EventBus::class => $this->eventBus,
            CommandBus::class => $this->commandBus,
        ];
        $this->container = ContainerBuilder::build($this->path, $this->namespace, $applicationDefinitions);
    }

    #[Override]
    public static function description(): string
    {
        return 'Run the command worker queue';
    }

    #[Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition();
    }

    #[Override]
    public function execute(): void
    {
        foreach ($this->commands as $command) {
            $this->log->info('Registering command handler', ['command' => $command]);
            $this->commandBus->registerCommandHandler($command, $this);
        }
        $this->log->info('Running command worker');
        $this->commandBus->run();
    }

    #[Override]
    public function handle(Command $command, CommandId $commandId, CorrelationId $correlationId, CausationId $causationId, ProcessId $processId): void
    {
        // TODO: Just how bad of an idea is this...
        $commandContainer = clone $this->container;
        $commandContainer->set(CorrelationId::class, $correlationId);
        $commandContainer->set(CausationId::class, CausationId::fromHex($processId->hex()));

        try {
            assert(method_exists($command, 'invoke'));
            $commandContainer->call($command->invoke(...));
            //            $this->log->info('Command succeeded', ['command' => $command::class]);
        } catch (Throwable $e) {
            $this->log->error('Command failed', ['exception' => $e]);
        }
        unset($command);
        unset($commandContainer);
        gc_collect_cycles();
    }
}
