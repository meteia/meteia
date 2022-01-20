<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Primitives;

use PhpSpec\ObjectBehavior;

/**
 * @mixin BooleanLiteral
 */
class BooleanLiteralSpec extends ObjectBehavior
{
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
