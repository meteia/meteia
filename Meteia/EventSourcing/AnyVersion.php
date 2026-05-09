<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\EventSourcing\Contracts\ExpectedVersion;

final readonly class AnyVersion implements ExpectedVersion
{
    #[\Override]
    public function assertCompatibleWith(StreamVersion $observed): void {}
}
