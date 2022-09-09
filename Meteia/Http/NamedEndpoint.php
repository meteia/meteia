<?php

declare(strict_types=1);

namespace Meteia\Http;

interface NamedEndpoint extends Endpoint
{
    public static function name(): string;
}
