<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Geography;

use PhpSpec\ObjectBehavior;

/**
 * @mixin Country
 */
class CountrySpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('US');
    }

    public function it_can_get_a_country_code()
    {
        $this->getCode()->shouldReturnString('US');
    }

    public function it_can_get_a_country_name()
    {
        $this->getName()->shouldReturnString('United States');
    }

    public function it_can_convert_to_a_string()
    {
        $this->__toString()->shouldReturnString('United States');
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMatchers(): array
    {
        return [
            'returnString' => function ($subject, $value) {
                $subject = '' . $subject;
                $value = '' . $value;
                if ($value === $subject) {
                    return true;
                }

                return false;
            },
        ];
    }
}
