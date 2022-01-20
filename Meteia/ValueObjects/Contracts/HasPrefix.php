<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Contracts;

interface HasPrefix
{
    public static function prefix(): string;
}
