<?php

declare(strict_types=1);

namespace Meteia\GraphQL\ClientAwareErrors;

use GraphQL\Error\ClientAware;

class Unauthorized extends \Exception implements ClientAware
{
    public function __construct()
    {
        parent::__construct('Unauthorized');
    }

    public function getCategory()
    {
        return 'authorization';
    }

    public function isClientSafe()
    {
        return true;
    }
}
