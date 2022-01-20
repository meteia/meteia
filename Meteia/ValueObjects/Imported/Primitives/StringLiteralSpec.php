<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Primitives;

use PhpSpec\ObjectBehavior;

/**
 * @mixin StringLiteral
 */
class StringLiteralSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('A Very Unique String');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(StringLiteral::class);
    }

    public function it_can_be_cast_as_string()
    {
        $this->shouldBeLike('A Very Unique String');
    }

    public function it_can_check_equality()
    {
        $this->caseInsensitiveEquals(new StringLiteral('A Very Unique String'))->shouldEqual(true);
        $this->caseInsensitiveEquals(new StringLiteral('A VERY UNIQUE STRING'))->shouldEqual(true);
        $this->caseInsensitiveEquals(new StringLiteral('A VERYDUNIQUE STRING'))->shouldEqual(false);

        $this->caseSensitiveEquals(new StringLiteral('A Very Unique String'))->shouldEqual(true);
        $this->caseSensitiveEquals(new StringLiteral('A Very unique String'))->shouldEqual(false);
        $this->caseSensitiveEquals(new StringLiteral('A VERYDUNIQUE STRING'))->shouldEqual(false);
    }
}
