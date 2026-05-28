<?php

declare(strict_types=1);

namespace Meteia\Realtime;

interface LiveViewSessionVerification
{
    public function accepted(): bool;
}
