<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Primitives;

use PhpSpec\ObjectBehavior;

/**
 * @mixin FloatLiteral
 */
class FloatLiteralSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(23.24);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FloatLiteral::class);
    }

    public function it_throws_an_exception_on_invalid_input()
    {
        $this->beConstructedWith('NaN');
        $this->shouldThrow()->duringInstantiation();
    }

    public function it_supports_basic_operations()
    {
        $this->add(new FloatLiteral(1))->shouldBeLike('24.24');
        $this->add(new FloatLiteral(-1))->shouldBeLike('22.24');

        $this->subtract(new FloatLiteral(1))->shouldBeLike('22.24');
        $this->subtract(new FloatLiteral(-1))->shouldBeLike('24.24');

        $this->multiplyBy(new FloatLiteral(2))->shouldBeLike('46.48');
        $this->multiplyBy(new FloatLiteral(-2))->shouldBeLike('-46.48');

        $this->divideBy(new FloatLiteral(2))->shouldBeLike('11.62');
        $this->divideBy(new FloatLiteral(-2))->shouldBeLike('-11.62');
    }

    public function it_can_be_compared()
    {
        $this->equalTo(new FloatLiteral(23.24))->shouldEqual(true);
        $this->equalTo(new FloatLiteral(23.242))->shouldEqual(false);
    }
}
