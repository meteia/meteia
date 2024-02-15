<?php

declare(strict_types=1);

namespace Meteia\Images;

interface Image
{
    public function dimensions(): array;

    public function gdImage(): \GdImage;
}
