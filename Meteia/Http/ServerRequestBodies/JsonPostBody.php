<?php

declare(strict_types=1);

namespace Meteia\Http\ServerRequestBodies;

use Meteia\Http\RequestBody;
use Override;

class JsonPostBody implements ServerRequestBody
{
    /** @var array<array-key, mixed> */
    private readonly array $data;

    public function __construct(RequestBody $requestBody)
    {
        $decoded = json_decode($requestBody->content(), true, 512, JSON_THROW_ON_ERROR);
        $this->data = is_array($decoded) ? $decoded : [];
    }

    public function value(string $name, mixed $default): mixed
    {
        return $this->data[$name] ?? $default;
    }

    #[Override]
    public function int($key, int $default): int
    {
        $value = $this->data[$key] ?? $default;
        if (!is_scalar($value)) {
            return $default;
        }

        return (int) $value;
    }

    #[Override]
    public function string($key, string $default): string
    {
        $value = $this->data[$key] ?? $default;

        return is_string($value) ? trim($value) : $default;
    }
}
