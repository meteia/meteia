<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Color;

use Meteia\Yeso\ValueObjects\WithMagicMethod;

class RGB
{
    use WithMagicMethod;

    private $red;

    private $green;

    private $blue;

    public function __construct(int $red, int $green, int $blue)
    {
        $this->red = intval($red);
        $this->green = intval($green);
        $this->blue = intval($blue);
    }

    public function asHex(): string
    {
        return strtoupper(str_pad(dechex($this->red), 2, '0') . str_pad(dechex($this->green), 2, '0') . str_pad(dechex($this->blue), 2, '0'));
    }

    public function asNumber(): int
    {
        return intval(hexdec($this->asHex()));
    }
}
