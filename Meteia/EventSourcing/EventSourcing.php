<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\Commands\Command;
use Meteia\Domain\CommandMessage;
use Meteia\Domain\CommandMessages;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\Contracts\UnitOfWorkContext;
use Meteia\Domain\ValueObjects\AggregateRootId;

use function strlen;

/**
 * Future me in like 18 hours...
 *
 * The reason why we have immutable... isn't for the reason I was thinking, lol... so why did we have it?
 *
 * Reasons immutable doesn't help:
 *  * Can't prevent command methods, like create/delete/start, from modifying the object directly (unless only Commands are allowed by convention to call in?)
 *  * There was a check in verifyReturnedObject() that attempted to prevent, something, but not sure how it was wired in
 */
trait EventSourcing
{
    /** @var EventMessage[] */
    protected array $__pendingEventMessages = [];

    /** @var CommandMessage[] */
    protected array $__pendingCommandMessages = [];

    protected int $__eventSequence = -1;

    public function causes(DomainEvent $event): void
    {
        $eventMessage = new EventMessage($this->aggregateRootId(), $event, $this->__eventSequence + 1);
        $eventMessage->applyTo($this);
        $this->__pendingEventMessages[] = $eventMessage;
    }

    public function wantsTo(Command $command): void
    {
        $commandMessage = new CommandMessage($this->aggregateRootId(), $command);
        $this->__pendingCommandMessages[] = $commandMessage;
    }

    public function commitInto(UnitOfWorkContext $unitOfWorkContext): void
    {
        $unitOfWorkContext->caused(new EventMessages($this->__pendingEventMessages));
        $unitOfWorkContext->wantsTo(new CommandMessages($this->__pendingCommandMessages));
        $this->__pendingEventMessages = [];
        $this->__pendingCommandMessages = [];
    }

    public function handleEventMessage(AggregateRootId $aggregateRootId, DomainEvent $event, int $eventSequence): void
    {
        if ($eventSequence <= $this->__eventSequence) {
            throw new \Exception('Event version is older than the aggregates version.');
        }
        $this->__eventSequence = $eventSequence;

        // $aggName = substr($this::class, strrpos($this::class, '\\') + 1);
        $eventName = substr($event::class, strrpos($event::class, '\\') + 1);
        $method = $eventName;
        // $trimMethodFrom = strpos($aggName, $eventName);
        // if ($trimMethodFrom !== false) {
        //    $method = substr($eventName, $trimMethodFrom + strlen($aggName));
        //    $method = lcfirst($method);
        // }
        $rc = new \ReflectionClass($this);
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

                throw new \Exception($err);
            }
            $args[$parameter->getPosition()] = $event->{$parameter->name};
        }
        \call_user_func_array([$this, $method], $args);
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
        $rc = new \ReflectionClass($this);
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

                throw new \Exception($err);
            }
            $args[$parameter->getPosition()] = $command->{$parameter->name};
        }
        \call_user_func_array([$this, $method], $args);
    }

    public function aggregateRootId(): AggregateRootId
    {
        $re = new \ReflectionClass($this);
        $firstParam = $re->getConstructor()->getParameters()[0];
        $type = (string) $firstParam->getType();
        if (!is_subclass_of($type, AggregateRootId::class)) {
            throw new \Exception('First param must be an AggregateRootId');
        }
        $name = $firstParam->getName();

        return $this->{$name} ?? throw new \Exception("{$name} must be a property on " . $this::class);
    }
}
