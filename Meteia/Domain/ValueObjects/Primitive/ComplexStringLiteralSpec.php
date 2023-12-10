<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Primitive;

use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;

/**
 * @mixin ComplexStringLiteral
 */
class ComplexStringLiteralSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('test 1');
    }

    public function it_can_return_the_same_string(): void
    {
        $this->isEmpty()->shouldReturn(false);
        $this->string()->shouldReturn('test 1');
    }

    public function it_can_take_two_string_as_input(): void
    {
        $this->beConstructedWith('test', ' 1');
        $this->string()->shouldReturn('test 1');
    }

    public function it_can_take_string_literal_as_constructor(): void
    {
        $string = new ComplexStringLiteral('test 1');
        $this->beConstructedWith($string);
        $this->string()->shouldReturn('test 1');
    }

    public function it_can_take_string_literal_and_string_as_input(): void
    {
        $string1 = new ComplexStringLiteral('test');
        $string3 = new ComplexStringLiteral('1');
        $this->beConstructedWith($string1, ' ', $string3);
        $this->string()->shouldReturn('test 1');
    }

    public function it_can_be_empty(): void
    {
        $this->beConstructedWith('');
        $this->isEmpty()->shouldReturn(true);
    }

    public function it_can_get_the_index_of(): void
    {
        $this->indexOf('blah')->shouldReturn(false);
        $this->indexOf(' ')->shouldReturn(4);
    }

    public function it_split_the_string(): void
    {
        $this->split(' ')->shouldHaveValue('test');
        $this->split(' ')->shouldHaveValue('1');
    }

    public function it_slice_the_string(): void
    {
        $this->slice(2)->shouldReturnComplexStringLiteral('st 1');
        $this->slice(-1)->shouldReturnComplexStringLiteral('1');
    }

    public function it_can_replace_sub_strings(): void
    {
        $this->beConstructedWith("<body text='%body%'>");
        $this->replace('%body%', 'black')->shouldReturnComplexStringLiteral("<body text='black'>");
    }

    public function it_can_trim_the_string(): void
    {
        $this->beConstructedWith(' test 1 ');
        $this->trim()->shouldReturnComplexStringLiteral('test 1');
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
            'returnComplexStringLiteral' => static function ($subject, $value) {
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
