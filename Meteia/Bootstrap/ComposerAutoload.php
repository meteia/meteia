<?php

declare(strict_types=1);

namespace Meteia\Bootstrap;

use Meteia\ValueObjects\Identity\FilesystemPath;

final readonly class ComposerAutoload
{
    public function __construct(
        private FilesystemPath $composerJson,
    ) {}

    public function primaryNamespace(): ApplicationNamespace
    {
        $composer = $this->composerJson->readJson();
        $psr4 = (array) ($composer->autoload->{'psr-4'} ?? []);
        $namespace = array_key_first($psr4);
        if ($namespace === null) {
            throw new MissingPrimaryNamespace($this->composerJson);
        }

        return new ApplicationNamespace(trim((string) $namespace, '\\'));
    }
}
