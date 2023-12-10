<?php

declare(strict_types=1);

namespace Meteia\Bluestone\Errors;

class ViewNotFound extends \Exception
{
    public function __construct(string $view)
    {
        $message = sprintf("View '%s' not found", $view);
        parent::__construct($message);
    }
}
