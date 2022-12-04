<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Color;

use PhpSpec\ObjectBehavior;

/** @mixin RGB */
class RGBSpec extends ObjectBehavior
{
    public function it_can_be_represented_as_a_number(): void
    {
        $this->beConstructedWith(255, 255, 255);
        $this->asNumber()->shouldReturn(16777215);
    }

    public function it_can_be_represented_as_hex(): void
    {
        $this->beConstructedWith(0, 127, 255);
        $this->asHex()->shouldReturn('007FFF');
    }

    public function it_throws_an_exception_on_non_numeric(): void
    {
        $this->beConstructedWith('a', 'b', 'c');
        $this->shouldThrow()->duringInstantiation();
    }
}
