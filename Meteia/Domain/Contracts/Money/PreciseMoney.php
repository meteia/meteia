<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts\Money;

interface PreciseMoney
{
    /**
     * @param mixed $precision
     *
     * @return RoundedMoney
     */
    public function round($precision = 2);
}
