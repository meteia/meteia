<?php

declare(strict_types=1);

namespace Meteia\GraphQL\ClientAwareErrors;

use Exception;
use GraphQL\Error\ClientAware;
use Override;

class InvalidScalarValue extends Exception implements ClientAware
{
    public function getCategory(): string
    {
        return 'values';
    }

    #[Override]
    public function isClientSafe(): bool
    {
        return true;
    }
}
