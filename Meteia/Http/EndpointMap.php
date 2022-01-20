<?php

declare(strict_types=1);

namespace Meteia\Http;

use Meteia\ValueObjects\Identity\Uri;

interface EndpointMap
{
    public function classNameFor(string $path): string;

    public function uri(string $endpoint): Uri;

    public function path(string $endpoint): string;
}
