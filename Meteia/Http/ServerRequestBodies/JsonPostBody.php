<?php

declare(strict_types=1);

namespace Meteia\Http\ServerRequestBodies;

use Meteia\Http\RequestBody;

class JsonPostBody implements ServerRequestBody
{
    private readonly array $data;

    public function __construct(RequestBody $requestBody)
    {
        $this->data = json_decode($requestBody->content(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function value(string $name, $default): mixed
    {
        return $this->data[$name] ?? $default;
    }

    #[\Override]
    public function int($key, int $default): int
    {
        return (int) ($this->data[$key] ?? $default);
    }

    #[\Override]
    public function string($key, string $default): string
    {
        return trim($this->data[$key] ?? $default);
    }
}
