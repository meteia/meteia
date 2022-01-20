<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Geography;

use PhpSpec\ObjectBehavior;

/**
 * @mixin CountryCodeName
 */
class CountryCodeNameSpec extends ObjectBehavior
{
    public function it_cat_get_a_country_name_by_code()
    {
        $this->getName(CountryCode::US)->shouldReturnString('United States');
    }

    public function it_cat_get_a_country_name_by_string()
    {
        $this->getName('US')->shouldReturnString('United States');
    }

    public function it_cat_get_a_country_code_by_name()
    {
        $this->getTwoLetterCode('United States')->shouldReturnString('US');
    }

    public function it_cat_get_no_country_code_by_fake_name()
    {
        $this->getTwoLetterCode('United Stat')->shouldReturnString('');
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
                } else {
                    throw new FailureException(sprintf('Message with subject "%s" and value "%s".', $subject, $value));
                }
            },
        ];
    }
}
