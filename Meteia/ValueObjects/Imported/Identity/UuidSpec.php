<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Identity;

use Meteia\Yeso\Exceptions\InvalidUuid;
use PhpSpec\ObjectBehavior;

/** @mixin Uuid */
class UuidSpec extends ObjectBehavior
{
    public function it_generates_a_uuid_if_none_given()
    {
        $this->__toString()->shouldMatch('/' . \Ramsey\Uuid\Uuid::VALID_PATTERN . '/');
    }

    public function it_accepts_uuids()
    {
        $this->beConstructedWith('8CB7AC7E-2B92-4D6F-B31E-298A29E2BBE8');
        $this->shouldBeLike('8CB7AC7E-2B92-4D6F-B31E-298A29E2BBE8');
    }

    public function it_throws_an_exception_if_given_invalid_input()
    {
        $this->beConstructedWith('OENCN0FDN0UtMkI5Mi00RDZGLUIzMUUtMjk4QTI5RTJCQkU4');
        $this->shouldThrow(InvalidUuid::class)->duringInstantiation();
    }
}
