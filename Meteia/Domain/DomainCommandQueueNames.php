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

readonly class DomainCommandQueueNames implements IteratorAggregate
{
    public function __construct(
        private ApplicationPath $applicationPath,
        private ApplicationNamespace $applicationNamespace,
    ) {
    }

    #[Override]
    public function getIterator(): Traversable
    {
        $classes = new PsrClasses($this->applicationPath, (string) $this->applicationNamespace, ['.+', 'DomainCommands', '.*\.php']);
        $commandClassnames = new ClassesImplementing($classes, DomainCommand::class);
        foreach ($commandClassnames as $commandClassname) {
            $parts = explode('\\', $commandClassname);
            $parts = array_filter($parts, fn ($part) => $part !== 'DomainCommands');
            $queueName = implode('.', $parts);

            yield $queueName => DomainCommand::class;
        }
    }
}
