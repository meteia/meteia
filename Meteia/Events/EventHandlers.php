<?php

declare(strict_types=1);

namespace Meteia\Events;

use Meteia\Application\ApplicationNamespace;
use Meteia\Application\ApplicationPath;
use Meteia\Classy\ClassesImplementing;
use Meteia\Classy\PsrClasses;
use Meteia\ValueObjects\Identity\FilesystemPath;

readonly class EventHandlers implements \IteratorAggregate
{
    public function __construct(
        private ApplicationPath $applicationPath,
        private ApplicationNamespace $applicationNamespace,
    ) {
    }

    #[\Override]
    public function getIterator(): \Traversable
    {
        $meteiaClasses = new PsrClasses(new FilesystemPath(__DIR__, '..', '..'), 'Meteia', ['.+', 'EventHandlers', '.*\.php']);
        foreach (new ClassesImplementing($meteiaClasses, EventHandler::class) as $class) {
            yield $class;
        }
        $appClasses = new PsrClasses($this->applicationPath, (string) $this->applicationNamespace, ['.+', 'EventHandlers', '.*\.php']);
        foreach (new ClassesImplementing($appClasses, EventHandler::class) as $class) {
            yield $class;
        }
    }
}
