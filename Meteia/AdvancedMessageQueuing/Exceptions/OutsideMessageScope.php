<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Exceptions;

final class OutsideMessageScope extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('No MessageScope is currently in flight; ensure work is done inside `using()`.');
    }
}
