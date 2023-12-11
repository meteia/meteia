<?php

declare(strict_types=1);

namespace Meteia\Events\CommandLine;

use Meteia\Application\ApplicationNamespace;
use Meteia\Application\ApplicationPath;
use Meteia\Application\ApplicationPublicDir;
use Meteia\CommandLine\Command;
use Meteia\Commands\CommandBus;
use Meteia\DependencyInjection\Container;
use Meteia\DependencyInjection\ContainerBuilder;
use Meteia\Events\Event;
use Meteia\Events\EventBus;
use Meteia\Events\EventHandler;
use Meteia\Events\EventId;
use Meteia\Events\EventToEventHandlersMap;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\ProcessId;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputDefinition;

readonly class RunWorker implements Command
{
    private Container $container;

    public function __construct(
        private EventBus $eventBus,
        private LoggerInterface $log,
        private ApplicationPath $path,
        private CommandBus $commandBus,
        private ApplicationNamespace $namespace,
        private ApplicationPublicDir $publicDir,
        private EventToEventHandlersMap $eventToEventHandlersMap,
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

    #[\Override]
    public static function description(): string
    {
        return 'Run the event worker queue';
    }

    #[\Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition();
    }

    #[\Override]
    public function execute(): void
    {
        foreach ($this->eventToEventHandlersMap as $event => $handlers) {
            foreach ($handlers as $handler) {
                $this->log->info('Registering event handler', ['event' => $event, 'handler' => $handler]);
                $this->eventBus->registerEventHandler($event, $handler, function (
                    Event $event,
                    EventId $eventId,
                    CorrelationId $correlationId,
                    CausationId $causationId,
                    ProcessId $processId,
                ) use ($handler): void {
                    $commandContainer = clone $this->container;
                    $commandContainer->set(CorrelationId::class, $correlationId);
                    $commandContainer->set(CausationId::class, CausationId::fromHex($processId->hex()));

                    /** @var EventHandler $eventHandler */
                    $eventHandler = $commandContainer->get($handler);

                    try {
                        $eventHandler->handle($event);
                        //                        $this->log->info('Event succeeded', ['event' => $event::class, 'handler' => $handler]);
                    } catch (\Throwable $e) {
                        $this->log->error('Event failed', ['exception' => $e]);
                    }
                    unset($eventHandler, $commandContainer);

                    gc_collect_cycles();
                });
            }
        }
        $this->log->info('Running event worker');
        $this->eventBus->run();
    }
}
