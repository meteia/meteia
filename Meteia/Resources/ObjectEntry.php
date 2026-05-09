<?php

declare(strict_types=1);

namespace Meteia\Resources;

final readonly class ObjectEntry implements EntryTarget
{
    public function __construct(
        private object $target,
        private bool $isReact = false,
    ) {}

    #[\Override]
    public function path(): string
    {
        $name = str_replace('\\', '/', $this->target::class);
        $extension = $this->isReact ? 'tsx' : 'ts';

        return '/' . $name . 'Entry.' . $extension;
    }
}
