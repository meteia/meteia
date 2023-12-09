<?php

declare(strict_types=1);

namespace Meteia\Domain;

use IteratorAggregate;
use Meteia\Application\ApplicationNamespace;
use Meteia\Application\ApplicationPath;
use Meteia\Classy\ClassesImplementing;
use Meteia\Classy\PsrClasses;
use Override;
use Traversable;

use function Meteia\Polyfills\array_map_assoc;

readonly class DomainEventQueueMappings implements IteratorAggregate
{
    public function __construct(
        private ApplicationPath $applicationPath,
        private ApplicationNamespace $applicationNamespace,
    ) {
    }

    #[Override]
    public function getIterator(): Traversable
    {
        $classes = new PsrClasses($this->applicationPath, (string) $this->applicationNamespace, ['.+', 'DomainEvents', '.*\.php']);
        $domainEventClassNames = iterator_to_array(new ClassesImplementing($classes, DomainEvent::class));

        $classes = new PsrClasses($this->applicationPath, (string) $this->applicationNamespace, ['.+', 'DomainEventHandlers', '.*\.php']);
        $domainEventHandlerClassNames = iterator_to_array(new ClassesImplementing($classes, DomainEventHandler::class));

        foreach ($domainEventClassNames as $eventClassName) {
            $exchangeName = $this->normalizedEventName($eventClassName);
            $queueNames = array_filter($domainEventHandlerClassNames, fn ($handlerClassname) => $exchangeName === $this->normalizedEventHandlerName($handlerClassname));
            $queueNames = array_map_assoc(fn ($key, $value) => [$this->normalizedEventHandlerName($value) => $value], $queueNames);

            yield new DomainEventToExchangeAndQueues(
                $eventClassName,
                $exchangeName,
                $queueNames,
            );
        }
    }

    private function normalizedEventName(string $className): string
    {
        $parts = explode('\\', $className);
        $parts = array_filter($parts, fn ($part) => !in_array($part, ['DomainEvents'], true));

        return implode('.', $parts);
    }

    private function normalizedEventHandlerName(string $className): string
    {
        $parts = explode('\\', $className);
        array_pop($parts);
        array_splice($parts, 1, 1);
        $parts = array_filter($parts, fn ($part) => !in_array($part, ['DomainEventHandlers'], true));

        return implode('.', $parts);
    }
}
