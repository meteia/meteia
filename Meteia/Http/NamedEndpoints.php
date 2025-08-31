<?php

declare(strict_types=1);

namespace Meteia\Http;

use function Meteia\Polyfills\array_map_assoc;

class NamedEndpoints
{
    use ClassBasedName;

    /**
     * @var array<string, NamedEndpoint>
     */
    private readonly array $endpoints;

    public function __construct(array $endpoints = [])
    {
        $endpoints = array_filter($endpoints, class_implements(...));
        $this->endpoints = array_map_assoc(fn(int $idx, string $endpoint) => [
            $this->classBasedName($endpoint) => $endpoint,
        ], $endpoints);
    }

    public function forKey(string $key): ?string
    {
        return $this->endpoints[$key];
    }

    public function keys(): array
    {
        return array_keys($this->endpoints);
    }
}
