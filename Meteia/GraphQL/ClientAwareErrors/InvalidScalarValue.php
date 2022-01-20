<?php

declare(strict_types=1);

namespace Meteia\GraphQL\ClientAwareErrors;

use GraphQL\Error\ClientAware;

class InvalidScalarValue extends \Exception implements ClientAware
{
    public function getCategory()
    {
        return 'values';
    }

    public function isClientSafe()
    {
        return true;
    }
}
