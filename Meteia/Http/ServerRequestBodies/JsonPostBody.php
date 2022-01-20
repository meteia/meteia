<?php

declare(strict_types=1);

namespace Meteia\Http\ServerRequestBodies;

use Psr\Http\Message\ServerRequestInterface;

class JsonPostBody implements ServerRequestBody
{
    private array $data;

    public function __construct(ServerRequestInterface $request)
    {
        $contents = $request->getBody()->getContents();
        $this->data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    }

    public function value(string $name, $default)
    {
        return $this->data[$name] ?? $default;
    }

    public function int($key, int $default): int
    {
        return (int) ($this->data[$key] ?? $default);
    }

    public function string($key, string $default): string
    {
        return trim($this->data[$key] ?? $default);
    }
}
