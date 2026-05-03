<?php

declare(strict_types=1);

namespace Meteia\Resources;

interface ManifestSource
{
    /**
     * @return array<string, array{file?: string, imports?: list<string>, css?: list<string>}>
     */
    public function entries(): array;
}
