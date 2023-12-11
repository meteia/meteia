<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects;

use Meteia\Yeso\Exceptions\ImproperType;
use Meteia\Yeso\Exceptions\ObjectMutationProhibited;
use Meteia\Yeso\Stubs\ImmutableArrayStub;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;

/**
 * @mixin ImmutableArray
 */
class ImmutableArraySpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beAnInstanceOf(ImmutableArrayStub::class);
    }

    /**
     * Eventually should be replaced with https://github.com/phpspec/phpspec/issues/1016.
     *
     * @source https://github.com/phpspec/phpspec/issues/379#issuecomment-148645255
     */
    public function getMatchers()
    {
        return [
            'generate' => static function ($subject, $value) {
                if (!$subject instanceof \Traversable) {
                    throw new FailureException('Return value should be instance of \Traversable');
                }
                $array = iterator_to_array($subject);

                return $array === $value;
            },
        ];
    }

    public function it_can_be_created_with_an_array(): void
    {
        $this->beConstructedWith([new \stdClass(), new \stdClass()]);
        $this->count()->shouldEqual(2);
    }

    public function it_can_be_created_with_an_empty_array(): void
    {
        $this->beConstructedWith([]);
        $this->count()->shouldEqual(0);
    }

    public function it_can_be_iterated(): void
    {
        $array = [new \stdClass(), new \stdClass()];
        $this->beConstructedWith($array);
        $this->shouldGenerate($array);
    }

    public function it_allows_pushing_of_valid_type(): void
    {
        $this->push(new \stdClass())->shouldHaveCount(1);
    }

    public function it_throws_when_pushing_an_invalid_type(): void
    {
        $this->shouldThrow(ImproperType::class)->during('push', [new \Exception()]);
    }

    public function it_allows_read_only_array_access(): void
    {
        $this->beConstructedWith([new \stdClass(), new \stdClass()]);
        $this[0]->shouldHaveType(\stdClass::class);
        $this->offsetExists(0)->shouldBe(true);
        $this->offsetExists(3)->shouldBe(false);
    }

    public function it_prohibits_mutations(): void
    {
        $this->shouldThrow(ObjectMutationProhibited::class)->during('offsetSet', [0, new \stdClass()]);
        $this->shouldThrow(ObjectMutationProhibited::class)->during('offsetUnset', [0]);
    }

    public function it_can_be_merged_with_another_array(): void
    {
        $this->appendArray([new \stdClass()])->shouldHaveCount(1);
        $this->appendArray([new \stdClass()])
            ->appendArray([new \stdClass()])
            ->shouldHaveCount(2)
        ;
        $this->appendArray([new \stdClass()])
            ->appendTraversable(new ImmutableArrayStub([new \stdClass()]))
            ->shouldHaveCount(2)
        ;
    }
}
