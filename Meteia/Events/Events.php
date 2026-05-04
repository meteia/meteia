<?php

declare(strict_types=1);

namespace Meteia\Events;

use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Classy\ClassesImplementing;
use Meteia\Classy\MergedClasses;
use Meteia\Classy\PsrClasses;
use Meteia\ValueObjects\Identity\FilesystemPath;

final readonly class Events implements \IteratorAggregate
{
    public function __construct(
        private ApplicationPath $applicationPath,
        private ApplicationNamespace $applicationNamespace,
    ) {}

    #[\Override]
    public function getIterator(): \Traversable
    {
        $regex = ['.+', 'Events', '.*\.php'];

        yield from new ClassesImplementing(
            new MergedClasses(
                new PsrClasses(new FilesystemPath(__DIR__, '..', '..'), 'Meteia', $regex),
                new PsrClasses($this->applicationPath, (string) $this->applicationNamespace, $regex),
            ),
            Event::class,
        );
    }
}
