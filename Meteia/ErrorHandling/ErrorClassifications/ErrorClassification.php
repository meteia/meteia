<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling\ErrorClassifications;

interface ErrorClassification
{
    public function isFatal(int $errorConstant): bool;
}
