<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Geography;

use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;

/**
 * @mixin Street
 */
class StreetSpec extends ObjectBehavior
{
    public function let()
    {
        $baseArray = [
            'addressNumberPrefix' => 'PRE',
            'addressNumberValue' => 'NUM',
            'addressNumberSuffix' => 'POST',
            'streetNamePreModifier' => '-MOD',
            'streetNamePreDirectional' => '-DIRECTION',
            'streetNamePreType' => '-TYPE',
            'streetNameValue' => 'STREET',
            'streetNamePostType' => 'TYPE-',
            'streetNamePostDirectional' => 'DIRECTION-',
            'streetNamePostModifier' => 'MOD-',
        ];
        $this->beConstructedWith($baseArray);
    }

    public function it_prints_the_full_number()
    {
        $this->getNumber()->shouldReturnStreet('PRE NUM POST');
    }

    public function it_prints_the_street_name()
    {
        $this->getStreetName()->shouldReturnStreet('-MOD -DIRECTION -TYPE STREET TYPE- DIRECTION- MOD-');
    }

    public function it_prints_the_full_street()
    {
        $this->getCompleteStreet()->shouldReturnStreet(
            'PRE NUM POST -MOD -DIRECTION -TYPE STREET TYPE- DIRECTION- MOD-',
        );
    }

    public function it_removes_white_space()
    {
        $baseArray = [
            'addressNumberPrefix' => '',
            'addressNumberValue' => 'NUM',
            'addressNumberSuffix' => '',
            'streetNamePreModifier' => '-',
            'streetNamePreDirectional' => '',
            'streetNamePreType' => '',
            'streetNameValue' => 'STREET',
            'streetNamePostType' => '',
            'streetNamePostDirectional' => '',
            'streetNamePostModifier' => '-',
        ];
        $this->beConstructedWith($baseArray);
        $this->getCompleteStreet()->shouldReturnStreet('NUM - STREET -');
    }

    public function it_can_create_from_number_and_name()
    {
        $this->beConstructedWith('302', 'Stone Ave');
        $this->getCompleteStreet()->shouldReturnStreet('302 Stone Ave');
    }

    public function it_can_create_from_name()
    {
        $this->beConstructedWith('302 E Stone Ave');
        $this->getCompleteStreet()->shouldReturnStreet('302 E Stone Ave');
        $this->__toString()->shouldReturnStreet('302 E Stone Ave');
    }

    public function it_can_create_from_blank_name()
    {
        $this->beConstructedWith('');
        $this->getCompleteStreet()->shouldReturnStreet('');
        $this->__toString()->shouldReturnStreet('');
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMatchers(): array
    {
        return [
            'haveValue' => function ($subject, $value) {
                $string = '';

                foreach ($subject as $maybe) {
                    $maybe = '' . $maybe;
                    $string .= '|' . $maybe;
                    if ($value === $maybe) {
                        return true;
                    }
                }

                return false;
            },
            'returnStreet' => function ($subject, $value) {
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
