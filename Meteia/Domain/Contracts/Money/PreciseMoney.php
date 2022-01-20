<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts\Money;

interface PreciseMoney
{
    /**
     * @return RoundedMoney
     */
    public function round($precision = 2);
}
