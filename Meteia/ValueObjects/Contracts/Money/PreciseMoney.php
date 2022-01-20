<?php

declare(strict_types=1);

namespace Meteia\Yeso\Contracts\Money;

interface PreciseMoney
{
    public function round(int $precision = 2): RoundedMoney;
}
