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
    public function let(): void
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

    public function it_prints_the_full_number(): void
    {
        $this->getNumber()->shouldReturnStreet('PRE NUM POST');
    }

    public function it_prints_the_street_name(): void
    {
        $this->getStreetName()->shouldReturnStreet('-MOD -DIRECTION -TYPE STREET TYPE- DIRECTION- MOD-');
    }

    public function it_prints_the_full_street(): void
    {
        $this->getCompleteStreet()->shouldReturnStreet(
            'PRE NUM POST -MOD -DIRECTION -TYPE STREET TYPE- DIRECTION- MOD-',
        );
    }

    public function it_removes_white_space(): void
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

    public function it_can_create_from_number_and_name(): void
    {
        $this->beConstructedWith('302', 'Stone Ave');
        $this->getCompleteStreet()->shouldReturnStreet('302 Stone Ave');
    }

    public function it_can_create_from_name(): void
    {
        $this->beConstructedWith('302 E Stone Ave');
        $this->getCompleteStreet()->shouldReturnStreet('302 E Stone Ave');
        $this->__toString()->shouldReturnStreet('302 E Stone Ave');
    }

    public function it_can_create_from_blank_name(): void
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
            'haveValue' => static function ($subject, $value) {
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
            'returnStreet' => static function ($subject, $value) {
                $subject = '' . $subject;
                $value = '' . $value;
                if ($value === $subject) {
                    return true;
                }

                throw new FailureException(sprintf('Message with subject "%s" and value "%s".', $subject, $value));
            },
        ];
    }
}
