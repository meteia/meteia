<?php

declare(strict_types=1);

namespace Meteia\Time;

use DateTimeImmutable;

interface Clock
{
    public function now(): DateTimeImmutable;
}
