<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Exception;
use Meteia\Commands\Command;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\Contracts\UnitOfWorkContext;
use Meteia\Domain\PendingCommand;
use Meteia\Domain\PendingCommands;
use Meteia\ValueObjects\AggregateRootId;
use Meteia\ValueObjects\Identity\UniqueId;
use ReflectionClass;

trait EventSourcing
{
    /** @var PendingEvent[] */
    protected array $__pendingEvents = [];

    /** @var PendingCommand[] */
    protected array $__pendingCommands = [];

    protected int $__eventSequence = -1;

    public function causes(DomainEvent $event): void
    {
        $streamId = $this->streamId();
        $version = new StreamVersion($this->__eventSequence + 1);
        $pending = new PendingEvent($streamId, $version, $event);
        $this->__pendingEvents[] = $pending;
        $this->handleEventMessage($streamId, $event, $version->asInt());
    }

    public function wantsTo(Command $command): void
    {
        $this->__pendingCommands[] = new PendingCommand($this->aggregateRootId(), $command);
    }

    public function commitInto(UnitOfWorkContext $unitOfWorkContext): void
    {
        $unitOfWorkContext->caused(new PendingEvents($this->__pendingEvents));
        $unitOfWorkContext->wantsTo(new PendingCommands($this->__pendingCommands));
        $this->__pendingEvents = [];
        $this->__pendingCommands = [];
    }

    public function handleEventMessage(UniqueId $streamId, DomainEvent $event, int $eventSequence): void
    {
        if ($eventSequence <= $this->__eventSequence) {
            throw new Exception('Event version is older than the aggregates version.');
        }
        $this->__eventSequence = $eventSequence;

        $eventName = substr($event::class, strrpos($event::class, '\\') + 1);
        $method = $eventName;
        $rc = new ReflectionClass($this);
        $meth = $rc->getMethod($method);
        $args = [];
        foreach ($meth->getParameters() as $parameter) {
            if (!isset($event->{$parameter->name})) {
                $err = sprintf(
                    'The event `%s` is missing the property named `%s` as expected by `%s->%s()`',
                    $event::class,
                    $parameter->name,
                    $rc->getName(),
                    $meth->name,
                );

                throw new Exception($err);
            }
            $args[$parameter->getPosition()] = $event->{$parameter->name};
        }
        $this->{$method}(...$args);
    }

    public function handleCommandMessage(Command $command): void
    {
        $aggName = substr($this::class, strrpos($this::class, '\\') + 1);
        $eventName = substr($command::class, strrpos($command::class, '\\') + 1);
        $method = $eventName;
        $trimMethodFrom = strpos($aggName, $eventName);
        if ($trimMethodFrom >= 0) {
            $method = substr($eventName, $trimMethodFrom + \strlen($aggName));
            $method = lcfirst($method);
        }
        $rc = new ReflectionClass($this);
        $meth = $rc->getMethod($method);
        $args = [];
        foreach ($meth->getParameters() as $parameter) {
            if (!isset($command->{$parameter->name})) {
                $err = sprintf(
                    'The event `%s` is missing the property named `%s` as expected by `%s->%s()`',
                    $command::class,
                    $parameter->name,
                    $rc->getName(),
                    $meth->name,
                );

                throw new Exception($err);
            }
            $args[$parameter->getPosition()] = $command->{$parameter->name};
        }
        $this->{$method}(...$args);
    }

    public function aggregateRootId(): AggregateRootId
    {
        $re = new ReflectionClass($this);
        $ctor = $re->getConstructor();
        \assert($ctor !== null);
        $firstParam = $ctor->getParameters()[0] ?? null;
        \assert($firstParam !== null);
        $type = (string) $firstParam->getType();
        if (!is_subclass_of($type, AggregateRootId::class)) {
            throw new Exception('First param must be an AggregateRootId');
        }
        $name = $firstParam->getName();
        $value = $this->{$name} ?? null;
        if (!$value instanceof AggregateRootId) {
            throw new Exception("{$name} must be an AggregateRootId property on " . $this::class);
        }

        return $value;
    }

    private function streamId(): StreamId
    {
        return new StreamId($this->aggregateRootId()->bytes());
    }
}
