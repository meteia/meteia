<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Identity;

use PhpSpec\ObjectBehavior;

/**
 * @mixin ImmutableURI
 */
class ImmutableURISpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('/');
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ImmutableURI::class);
    }

    public function it_appends_a_path(): void
    {
        $this->withPath('/potato')->__toString()->shouldEqual('/potato');
    }

    public function it_appends_a_query_string(): void
    {
        $this->withQuery(['food' => 'potato'])
            ->__toString()
            ->shouldEqual('/?food=potato')
        ;
        $this->withPath('/hmm')
            ->withQuery(['food' => 'potato'])
            ->__toString()
            ->shouldEqual('/hmm?food=potato')
        ;
    }
}
