<?php

declare(strict_types=1);

namespace Meteia\Bootstrap;

use Meteia\ValueObjects\Identity\FilesystemPath;

final class MissingPrimaryNamespace extends \DomainException
{
    public function __construct(FilesystemPath $composerJson)
    {
        parent::__construct("No PSR-4 namespace defined in {$composerJson}");
    }
}
