<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Money;

use PhpSpec\ObjectBehavior;

/**
 * @mixin RoundedUSD
 */
class RoundedUSDSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(4.12);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(RoundedUSD::class);
    }

    public function it_can_be_cast_to_a_string(): void
    {
        $this->shouldBeLike('4.12');
    }

    public function it_can_be_compared(): void
    {
        $this->equalTo(new RoundedUSD(4.12))->shouldEqual(true);
        $this->equalTo(new RoundedUSD(3.12))->shouldEqual(false);
    }
}
