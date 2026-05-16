<?php

declare(strict_types=1);

namespace Meteia\Events;

use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Classy\ClassesImplementing;
use Meteia\Classy\MergedClasses;
use Meteia\Classy\PsrClasses;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\ValueObjects\Identity\FilesystemPath;
use Override;
use Traversable;

final readonly class PsrEvents implements Events
{
    public function __construct(
        private ApplicationPath $applicationPath,
        private ApplicationNamespace $applicationNamespace,
    ) {}

    #[Override]
    public function getIterator(): Traversable
    {
        $regex = ['.+', 'Events', '.*\.php'];

        yield from new ClassesImplementing(
            new MergedClasses(
                new PsrClasses(new FilesystemPath(__DIR__, '..', '..'), 'Meteia', $regex),
                new PsrClasses($this->applicationPath, (string) $this->applicationNamespace, $regex),
            ),
            DomainEvent::class,
        );
    }
}
