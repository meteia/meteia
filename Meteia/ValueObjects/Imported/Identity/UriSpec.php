<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Identity;

use PhpSpec\ObjectBehavior;

/**
 * @mixin Uri
 */
class UriSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('http://example.com');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Uri::class);
    }

    public function it_appends_a_path()
    {
        $this->withPath('/potato')->shouldBeLike('http://example.com/potato');
    }

    public function it_prepends_a_forward_slash_to_paths()
    {
        $this->withPath('potato')->shouldBeLike('http://example.com/potato');
    }

    public function it_appends_a_query_array()
    {
        $this->withQueryArray(['food' => 'potato'])->shouldBeLike('http://example.com/?food=potato');
        $this->withPath('/hmm')->withQueryArray(['food' => 'potato'])->shouldBeLike('http://example.com/hmm?food=potato');
    }

    public function it_appends_a_query_string()
    {
        $this->withQueryString('food=potato')->shouldBeLike('http://example.com/?food=potato');
        $this->withPath('/hmm')->withQueryString('food=potato&color=green')->shouldBeLike('http://example.com/hmm?food=potato&color=green');
    }

    public function it_returns_a_uri_with_a_scheme()
    {
        $this->withScheme('https')->shouldBeLike('https://example.com');
    }

    public function it_returns_a_uri_with_a_port()
    {
        $this->withPort('7443')->shouldBeLike('http://example.com:7443');
    }

    public function it_returns_a_uri_with_a_host()
    {
        $this->withHost('example.org')->shouldBeLike('http://example.org');
    }

    public function it_returns_a_uri_with_a_fragment()
    {
        $this->withFragment('yellow')->shouldBeLike('http://example.com/#yellow');
    }
}
