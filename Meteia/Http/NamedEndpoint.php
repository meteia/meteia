<?php

declare(strict_types=1);

namespace Meteia\Http;

interface NamedEndpoint
{
    public static function name(): string;
}
