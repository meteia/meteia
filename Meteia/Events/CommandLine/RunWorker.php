<?php

declare(strict_types=1);

namespace Meteia\Events\CommandLine;

use Meteia\CommandLine\Command;
use Meteia\DependencyInjection\Container;
use Meteia\Events\EventInbox;
use Meteia\Events\EventSink;
use Meteia\Events\EventToEventSinksMap;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputDefinition;

final readonly class RunWorker implements Command
{
    public function __construct(
        private EventInbox $eventInbox,
        private LoggerInterface $log,
        private Container $container,
        private EventToEventSinksMap $eventToEventSinksMap,
    ) {}

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
        foreach ($this->eventToEventSinksMap as $event => $sinks) {
            foreach ($sinks as $sinkClass) {
                $this->log->info('Registering event sink', ['event' => $event, 'sink' => $sinkClass]);
                /** @var EventSink $sink */
                $sink = $this->container->get($sinkClass);
                $this->eventInbox->subscribe($event, $sinkClass, $sink);
            }
        }
        $this->log->info('Running event worker');
        $this->eventInbox->run();
    }
}
