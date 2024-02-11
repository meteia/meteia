<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Primitives;

use PhpSpec\ObjectBehavior;

/**
 * @mixin IntegerLiteral
 */
class IntegerLiteralSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(23);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(IntegerLiteral::class);
    }

    public function it_throws_an_exception_on_invalid_input(): void
    {
        $this->beConstructedWith('NaN');
        $this->shouldThrow()->duringInstantiation();
    }

    public function it_supports_basic_operations(): void
    {
        $this->add(new IntegerLiteral(1))->shouldBeLike('24');
        $this->add(new IntegerLiteral(-1))->shouldBeLike('22');

        $this->subtract(new IntegerLiteral(1))->shouldBeLike('22');
        $this->subtract(new IntegerLiteral(-1))->shouldBeLike('24');

        $this->multiplyBy(new IntegerLiteral(2))->shouldBeLike('46');
        $this->multiplyBy(new IntegerLiteral(-2))->shouldBeLike('-46');

        $this->divideBy(new IntegerLiteral(2))->shouldBeLike('11');
        $this->divideBy(new IntegerLiteral(-2))->shouldBeLike('-11');
    }

    public function it_can_be_compared(): void
    {
        $this->equalTo(new IntegerLiteral(23))->shouldEqual(true);
        $this->equalTo(new IntegerLiteral(24))->shouldEqual(false);
    }

    public function it_can_be_cast_as_a_float(): void
    {
        $this->asFloat()->equalTo(new FloatLiteral(23))->shouldEqual(true);
    }
}
