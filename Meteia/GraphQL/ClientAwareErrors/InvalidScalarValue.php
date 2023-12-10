<?php

declare(strict_types=1);

namespace Meteia\GraphQL\ClientAwareErrors;

use GraphQL\Error\ClientAware;

class InvalidScalarValue extends \Exception implements ClientAware
{
    public function getCategory(): string
    {
        return 'values';
    }

    public function isClientSafe(): bool
    {
        return true;
    }
}
