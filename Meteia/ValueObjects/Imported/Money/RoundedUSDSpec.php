<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Money;

use PhpSpec\ObjectBehavior;

/**
 * @mixin RoundedUSD
 */
class RoundedUSDSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(4.12);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RoundedUSD::class);
    }

    public function it_can_be_cast_to_a_string()
    {
        $this->shouldBeLike('4.12');
    }

    public function it_can_be_compared()
    {
        $this->equalTo(new RoundedUSD(4.12))->shouldEqual(true);
        $this->equalTo(new RoundedUSD(3.12))->shouldEqual(false);
    }
}
