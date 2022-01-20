<?php

declare(strict_types=1);

namespace Meteia\Dulce\ErrorClassifications;

class StrictErrorClassification implements ErrorClassification
{
    public function isFatal(int $errorConstant): bool
    {
        return true;
    }
}
