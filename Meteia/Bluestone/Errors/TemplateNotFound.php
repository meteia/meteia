<?php

declare(strict_types=1);

namespace Meteia\Bluestone\Errors;

class TemplateNotFound extends \Exception
{
    public function __construct(string $view)
    {
        $message = sprintf("Template not found for view '%s' (or parent classes if applicable)", $view);
        parent::__construct($message);
    }
}
