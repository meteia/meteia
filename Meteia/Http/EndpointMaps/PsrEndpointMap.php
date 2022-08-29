<?php

declare(strict_types=1);

namespace Meteia\Http\EndpointMaps;

use Doctrine\Inflector\Inflector;
use Meteia\Application\ApplicationNamespace;
use Meteia\Http\Endpoint;
use Meteia\Http\EndpointMap;
use Meteia\Http\Host;
use Meteia\ValueObjects\Identity\Uri;

class PsrEndpointMap implements EndpointMap
{
    public function __construct(
        private readonly Inflector $inflector,
        private readonly ApplicationNamespace $namespace,
        private readonly Host $host,
    ) {
    }

    public function classNameFor(string $path): string
    {
        $parts = array_values(array_filter(explode('/', $path)));
        array_splice($parts, 1, 0, 'Endpoints');
        array_unshift($parts, (string) $this->namespace);
        $parts = array_map($this->inflector->classify(...), $parts);

        return implode('\\', $parts);
    }

    public function path(string $endpoint): string
    {
        if (!is_subclass_of($endpoint, Endpoint::class)) {
            throw new \Exception('Invalid Endpoint');
        }
        $parts = explode('\\', $endpoint);
        array_shift($parts);
        array_splice($parts, 1, 1);

        $path = '/' . implode('/', $parts);
        $path = preg_replace('~(?<=\\w)([A-Z])~u', '-$1', $path);
        $path = mb_strtolower($path);

        return $path;
    }

    public function uri(string $endpoint): Uri
    {
        return $this->host->withPath($this->path($endpoint));
    }

    private function classify(string $slug): string
    {
        return $slug;
    }
}
