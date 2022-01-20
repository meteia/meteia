<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects;

use Meteia\Yeso\Exceptions\MethodNotFound;
use Meteia\Yeso\Stubs\WithMagicMethodStub;
use PhpSpec\ObjectBehavior;

/**
 * @mixin WithMagicMethod
 */
class WithMagicMethodSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beAnInstanceOf(WithMagicMethodStub::class);
    }

    public function it_provides_a_with_magic_method()
    {
        $this->withKey('green')->key->shouldEqual('green');
    }

    public function it_provides_a_with_magic_method_alt()
    {
        $this->shouldThrow(MethodNotFound::class)->during('unknown');
    }
}
