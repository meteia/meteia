<?php

declare(strict_types=1);

namespace Meteia\Dulce\ErrorClassifications;

interface ErrorClassification
{
    public function isFatal(int $errorConstant): bool;
}
