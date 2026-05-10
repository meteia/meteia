<?php

declare(strict_types=1);

namespace Meteia\Time;

interface Clock
{
    public function now(): \DateTimeImmutable;
}
