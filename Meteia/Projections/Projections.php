<?php

declare(strict_types=1);

namespace Meteia\Projections;

use IteratorAggregate;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Classy\ClassesImplementing;
use Meteia\Classy\MergedClasses;
use Meteia\Classy\PsrClasses;
use Meteia\Projections\Contracts\Projection;
use Meteia\ValueObjects\Identity\FilesystemPath;
use Override;
use Traversable;

/**
 * @implements IteratorAggregate<array-key, class-string<Projection>>
 */
final readonly class Projections implements IteratorAggregate
{
    public function __construct(
        private ApplicationPath $applicationPath,
        private ApplicationNamespace $applicationNamespace,
    ) {}

    #[Override]
    public function getIterator(): Traversable
    {
        $regex = ['.+', '.*Projection\.php'];

        yield from new ClassesImplementing(
            new MergedClasses(
                new PsrClasses(new FilesystemPath(__DIR__, '..', '..'), 'Meteia', $regex),
                new PsrClasses($this->applicationPath, (string) $this->applicationNamespace, $regex),
            ),
            Projection::class,
        );
    }
}
