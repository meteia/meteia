<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Identity;

use PhpSpec\ObjectBehavior;

/**
 * @mixin ImmutableURI
 */
class ImmutableURISpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('/');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ImmutableURI::class);
    }

    public function it_appends_a_path()
    {
        $this->withPath('/potato')->__toString()->shouldEqual('/potato');
    }

    public function it_appends_a_query_string()
    {
        $this->withQuery(['food' => 'potato'])->__toString()->shouldEqual('/?food=potato');
        $this->withPath('/hmm')->withQuery(['food' => 'potato'])->__toString()->shouldEqual('/hmm?food=potato');
    }
}
