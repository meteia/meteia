<?php

declare(strict_types=1);

namespace Meteia\Dulce\ErrorClassifications;

class LaxErrorClassification implements ErrorClassification
{
    public function isFatal(int $errorConstant): bool
    {
        switch ($errorConstant) {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                return true;
        }

        return false;
    }
}
