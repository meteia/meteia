<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling\ErrorClassifications;

use Override;

class StrictErrorClassification implements ErrorClassification
{
    #[Override]
    public function isFatal(int $errorConstant): bool
    {
        return true;
    }
}
