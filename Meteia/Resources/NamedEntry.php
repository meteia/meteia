<?php

declare(strict_types=1);

namespace Meteia\Resources;

final readonly class NamedEntry implements EntryTarget
{
    public function __construct(
        private string $name,
        private bool $isReact = false,
    ) {}

    public function path(): string
    {
        $extension = $this->isReact ? 'tsx' : 'ts';

        return '/' . $this->name . 'Entry.' . $extension;
    }
}
