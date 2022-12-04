<?php

declare(strict_types=1);

namespace Meteia\GraphQL\ClientAwareErrors;

use Exception;
use GraphQL\Error\ClientAware;

class Unauthorized extends Exception implements ClientAware
{
    public function __construct()
    {
        parent::__construct('Unauthorized');
    }

    public function getCategory(): string
    {
        return 'authorization';
    }

    public function isClientSafe(): bool
    {
        return true;
    }
}
