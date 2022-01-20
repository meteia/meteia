<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Money;

use PhpSpec\ObjectBehavior;

/**
 * @mixin PreciseUSD
 */
class PreciseUSDSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('3.14159');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(PreciseUSD::class);
    }

    public function it_can_be_cast_as_a_string()
    {
        $this->shouldBeLike('3.14159');
    }

    public function it_can_be_rounded()
    {
        $this->round()->shouldHaveType(RoundedUSD::class);
    }

    public function it_rounds_to_correct_precisions()
    {
        $this->round(0)->__toString()->shouldBeLike('3');
        $this->round(1)->__toString()->shouldBeLike('3.1');
        $this->round(2)->__toString()->shouldBeLike('3.14');
    }

    public function it_can_be_compared()
    {
        $this->equalTo(new PreciseUSD(3.14159))->shouldEqual(true);
        $this->equalTo(new PreciseUSD(3.141592))->shouldEqual(false);
    }

    public function it_supports_basic_operations()
    {
        $this->add(new PreciseUSD(1))->shouldBeLike('4.14159');
        $this->add(new PreciseUSD(-1))->shouldBeLike('2.14159');

        $this->subtract(new PreciseUSD(1))->shouldBeLike('2.14159');
        $this->subtract(new PreciseUSD(-1))->shouldBeLike('4.14159');

        $this->multiplyBy(new PreciseUSD(2))->shouldBeLike('6.28318');
        $this->multiplyBy(new PreciseUSD(-2))->shouldBeLike('-6.28318');

        $this->divideBy(new PreciseUSD(2))->shouldBeLike('1.570795');
        $this->divideBy(new PreciseUSD(-2))->shouldBeLike('-1.570795');
    }
}
