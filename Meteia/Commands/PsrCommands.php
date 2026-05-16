<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Classy\ClassesImplementing;
use Meteia\Classy\MergedClasses;
use Meteia\Classy\PsrClasses;
use Meteia\ValueObjects\Identity\FilesystemPath;
use Override;
use Traversable;

final readonly class PsrCommands implements Commands
{
    public function __construct(
        private ApplicationPath $applicationPath,
        private ApplicationNamespace $applicationNamespace,
    ) {}

    #[Override]
    public function getIterator(): Traversable
    {
        $regex = ['.+', 'Commands', '.*\.php'];

        yield from new ClassesImplementing(
            new MergedClasses(
                new PsrClasses(new FilesystemPath(__DIR__, '..', '..'), 'Meteia', $regex),
                new PsrClasses($this->applicationPath, (string) $this->applicationNamespace, $regex),
            ),
            Command::class,
        );
    }
}
