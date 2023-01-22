<?php

declare(strict_types=1);

namespace Meteia\Http\ServerRequestBodies;

use Meteia\Http\RequestBody;

class FormPostBody implements ServerRequestBody
{
    private readonly array $data;

    public function __construct(RequestBody $request)
    {
        $contents = $request->content();
        parse_str($contents, $data);
        $this->data = $data;
    }

    public function all(): array
    {
        return $this->data;
    }

    public function int($key, int $default): int
    {
        return (int) ($this->data[$key] ?? $default);
    }

    public function string($key, string $default): string
    {
        return trim($this->data[$key] ?? $default);
    }

    public function bool($key, bool $default): bool
    {
        if (!isset($this->data[$key])) {
            return $default;
        }

        if (in_array($this->data[$key], ['yes', '1', 'on', 'true', 1, true], true)) {
            return true;
        }
        if (in_array($this->data[$key], ['no', '0', 'off', 'false', 0, false], true)) {
            return false;
        }

        return $default;
    }
}
