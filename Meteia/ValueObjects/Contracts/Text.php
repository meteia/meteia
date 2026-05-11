<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Contracts;

use JsonSerializable;
use Meteia\Domain\Contracts\Comparable;
use Override;
use Stringable;

interface Text extends Stringable, JsonSerializable, Comparable
{
    #[Override]
    public function toNative(): string;
}
