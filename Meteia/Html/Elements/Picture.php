<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

readonly class Picture
{
    /**
     * @param array<int, Source> $sources
     */
    public function __construct(
        public Img $img,
        public array $sources,
    ) {}

    public function __toString(): string
    {
        return \sprintf(
            '<%s>%s</%s>' . PHP_EOL,
            'picture',
            implode("\n", array_map('strval', [...$this->sources, $this->img])),
            'picture',
        );
    }
}
