<?php

declare(strict_types=1);

namespace Meteia\Events\CommandLine;

use InvalidArgumentException;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\CommandLine\Command;
use Meteia\CommandLine\PayloadParser;
use Meteia\DependencyInjection\Container;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Events\EventInbox;
use Meteia\Events\EventSink;
use Meteia\Events\EventSinks;
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
        private EventSinks $eventSinks,
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
                'Only subscribe to the specified dotted event class, e.g. App.Users.Events.UserRegistered',
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
        \assert($input instanceof InputInterface, 'console input must be available in the event worker container');
        $namespace = $this->container->get(ApplicationNamespace::class);
        \assert($namespace instanceof ApplicationNamespace, 'application namespace must be available in the event worker container');

        $only = $input->getOption('only');
        $once = (bool) $input->getOption('once');

        $targetEvent = null;
        if ($only !== null) {
            $target = (string) $only;
            $parser = new PayloadParser();
            $targetEvent = $parser->resolve($target, $namespace, DomainEvent::class);
            if ($targetEvent === null) {
                throw new InvalidArgumentException(sprintf(
                    'Target "%s" must resolve to a class implementing %s',
                    $target,
                    DomainEvent::class,
                ));
            }
        }

        foreach ($this->eventSinks as $event => $sinks) {
            if ($targetEvent !== null && $event !== $targetEvent) {
                continue;
            }
            $suffix = $targetEvent !== null ? ' (only)' : '';
            foreach ($sinks as $sinkClass) {
                $this->log->info('Registering event sink' . $suffix, ['event' => $event, 'sink' => $sinkClass]);
                $sink = $this->container->get($sinkClass);
                \assert($sink instanceof EventSink, 'event sink class must resolve to an EventSink');
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
