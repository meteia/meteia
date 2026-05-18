<?php

declare(strict_types=1);

namespace Meteia\Http;

use Meteia\Classy\ClassBasedName;

use function Meteia\Polyfills\array_map_assoc;

class NamedEndpoints
{
    /**
     * @var array<string, class-string<NamedEndpoint>>
     */
    private readonly array $endpoints;

    /**
     * @param array<int, class-string<NamedEndpoint>> $endpoints
     */
    public function __construct(array $endpoints = [])
    {
        $endpoints = array_filter(
            $endpoints,
            static fn(string $endpoint): bool => (
                class_exists($endpoint) && is_subclass_of($endpoint, NamedEndpoint::class)
            ),
        );
        /** @var array<string, class-string<NamedEndpoint>> $mapped */
        $mapped = array_map_assoc(static fn(int $idx, string $endpoint) => [
            (string) new ClassBasedName($endpoint) => $endpoint,
        ], $endpoints);
        $this->endpoints = $mapped;
    }

    public function forKey(string $key): ?string
    {
        return $this->endpoints[$key] ?? null;
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys($this->endpoints);
    }
}
