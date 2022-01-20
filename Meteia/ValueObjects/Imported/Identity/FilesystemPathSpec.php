<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Identity;

use PhpSpec\ObjectBehavior;

class FilesystemPathSpec extends ObjectBehavior
{
    public function it_accepts_a_path()
    {
        $this->beConstructedWith('/tmp');
        $this->shouldBeLike('/tmp');
    }

    public function it_joins_path()
    {
        $this->beConstructedWith('/', 'tmp');
        $this->shouldBeLike('/tmp');
    }

    public function it_appends_path()
    {
        $this->beConstructedWith('/');
        $this->join('tmp')->shouldBeLike('/tmp');
    }
}
