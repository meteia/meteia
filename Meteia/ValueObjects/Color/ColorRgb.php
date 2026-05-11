<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Color;

class ColorRgb
{
    public function __construct(
        public int $red,
        public int $green,
        public int $blue,
    ) {}

    public function __toString(): string
    {
        return '#' . $this->asHex();
    }

    public function asHex(): string
    {
        return (
            str_pad(dechex($this->red), 2, '0')
            . str_pad(dechex($this->green), 2, '0')
            . str_pad(dechex($this->blue), 2, '0')
        );
    }

    public function asInteger(): int|float
    {
        return hexdec($this->asHex());
    }
}
