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
use UnexpectedValueException;

final readonly class RunWorker implements Command
{
    public function __construct(
        private EventInbox $eventInbox,
        private LoggerInterface $log,
        private Container $container,
        private EventSinks $eventSinks,
        private InputInterface $input,
        private ApplicationNamespace $namespace,
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
        $only = $this->onlyOption();
        $once = $this->onceOption();

        $targetEvent = null;
        if ($only !== null) {
            $target = $only;
            $parser = new PayloadParser();
            $targetEvent = $parser->resolve($target, $this->namespace, DomainEvent::class);
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
                $this->eventInbox->subscribe($event, $sinkClass, $this->eventSink($sinkClass));
            }
        }

        $this->log->info('Running event worker' . ($once ? ' (once)' : ''));
        if ($once) {
            $this->eventInbox->runOnce();

            return;
        }
        $this->eventInbox->run();
    }

    private function onlyOption(): ?string
    {
        return $this->optionalString($this->input->getOption('only'));
    }

    private function onceOption(): bool
    {
        return $this->input->getOption('once') === true;
    }

    private function eventSink(string $sinkClass): EventSink
    {
        return $this->resolvedEventSink($this->container->get($sinkClass));
    }

    private function optionalString(mixed $value): ?string
    {
        if ($value === null || \is_string($value)) {
            return $value;
        }

        throw new UnexpectedValueException('event worker only option must be a string when provided');
    }

    private function resolvedEventSink(mixed $sink): EventSink
    {
        if ($sink instanceof EventSink) {
            return $sink;
        }

        throw new UnexpectedValueException('event sink class must resolve to an EventSink');
    }
}
