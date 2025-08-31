<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Color;

class ColorRGB
{
    public $red;

    public $green;

    public $blue;

    public function __construct(int $red, int $green, int $blue)
    {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }

    public function __toString()
    {
        return '#' . $this->asHex();
    }

    public function asHex()
    {
        return (
            str_pad(dechex($this->red), 2, '0')
            . str_pad(dechex($this->green), 2, '0')
            . str_pad(dechex($this->blue), 2, '0')
        );
    }

    public function asInteger()
    {
        return hexdec($this->asHex());
    }
}
