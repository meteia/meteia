<?php

declare(strict_types=1);

namespace Meteia\Events\CommandLine;

use InvalidArgumentException;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\CommandLine\Command;
use Meteia\CommandLine\PayloadParser;
use Meteia\DependencyInjection\Container;
use Meteia\Events\Event;
use Meteia\Events\EventInbox;
use Meteia\Events\EventSink;
use Meteia\Events\EventToEventSinksMap;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final readonly class RunWorker implements Command
{
    public function __construct(
        private EventInbox $eventInbox,
        private LoggerInterface $log,
        private Container $container,
        private EventToEventSinksMap $eventToEventSinksMap,
    ) {}

    #[Override]
    public static function description(): string
    {
        return 'Run the event worker queue.';
    }

    #[Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputOption(
                'only',
                '',
                InputOption::VALUE_REQUIRED,
                'Only subscribe to the specified dotted event class, e.g. App.Events.UserRegistered',
            ),
            new InputOption(
                'once',
                '',
                InputOption::VALUE_NONE,
                'Exit after handling one event (typically used with --only)',
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

        $targetEvent = null;
        if ($only !== null) {
            $target = (string) $only;
            $parser = new PayloadParser();
            $targetEvent = $parser->resolve($target, $namespace, Event::class);
            if ($targetEvent === null) {
                throw new InvalidArgumentException(sprintf(
                    'Target "%s" must resolve to a class implementing %s',
                    $target,
                    Event::class,
                ));
            }
        }

        foreach ($this->eventToEventSinksMap as $event => $sinks) {
            if ($targetEvent !== null && $event !== $targetEvent) {
                continue;
            }
            $suffix = $targetEvent !== null ? ' (only)' : '';
            foreach ($sinks as $sinkClass) {
                $this->log->info('Registering event sink' . $suffix, ['event' => $event, 'sink' => $sinkClass]);
                /** @var EventSink $sink */
                $sink = $this->container->get($sinkClass);
                $this->eventInbox->subscribe($event, $sinkClass, $sink);
            }
        }

        $this->log->info('Running event worker' . ($once ? ' (once)' : ''));
        if ($once) {
            $this->eventInbox->runOnce();

            return;
        }
        $this->eventInbox->run();
    }
}
