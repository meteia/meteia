<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Primitive;

use PhpSpec\ObjectBehavior;

/**
 * @mixin Boolean
 */
class BooleanSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(true);
    }

    public function it_acts_correctly_with_true()
    {
        $this->beConstructedWith(true);
        $this->isTrue()->shouldReturn(true);
        $this->isFalse()->shouldReturn(false);
    }

    public function it_acts_correctly_with_false()
    {
        $this->beConstructedWith(false);
        $this->isTrue()->shouldReturn(false);
        $this->isFalse()->shouldReturn(true);
    }
}
