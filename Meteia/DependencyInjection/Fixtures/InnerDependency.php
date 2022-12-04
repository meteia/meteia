<?php

declare(strict_types=1);

namespace Meteia\DependencyInjection\Fixtures;

use DateTime;

class InnerDependency
{
    public function __construct(
        public DateTime $dateTime,
        public string $option = '',
    ) {
    }

    public function reverse(string $text): string
    {
        return strrev($text);
    }
}
