<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Contracts;

use Meteia\Domain\Contracts\Comparable;

interface Text extends \Stringable, \JsonSerializable, Comparable
{
    public function toNative(): string;
}
