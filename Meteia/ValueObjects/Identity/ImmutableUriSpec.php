<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use PhpSpec\ObjectBehavior;

/**
 * @mixin ImmutableUri
 */
class ImmutableUriSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('/');
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ImmutableUri::class);
    }

    public function it_appends_a_path(): void
    {
        $this->withPath('/potato')->__toString()->shouldEqual('/potato');
    }

    public function it_appends_a_query_string(): void
    {
        $this->withQuery('food=potato')->__toString()->shouldEqual('/?food=potato');
        $this->withPath('/hmm')->withQuery('food=potato')->__toString()->shouldEqual('/hmm?food=potato');
    }

    public function it_round_trips_a_full_url(): void
    {
        $this->beConstructedWith('https://user:pass@example.com:8080/foo/bar?q=1#frag');
        $this->__toString()->shouldEqual('https://user:pass@example.com:8080/foo/bar?q=1#frag');
    }

    public function it_preserves_multi_segment_paths(): void
    {
        $this->withPath('/a/b/c')->__toString()->shouldEqual('/a/b/c');
    }

    public function it_omits_password_when_null(): void
    {
        $this->beConstructedWith('http://example.com');
        $this->withUserInfo('alice')->__toString()->shouldEqual('http://alice@example.com');
    }
}
