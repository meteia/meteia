<?php

declare(strict_types=1);

namespace Meteia\Http\ServerRequestBodies;

use Meteia\Library\StringCase;
use Psr\Http\Message\ServerRequestInterface;

readonly class FormPostBody implements ServerRequestBody
{
    private array $data;

    public function __construct(ServerRequestInterface $request)
    {
        $this->data = $request->getParsedBody();
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

        if (\in_array($this->data[$key], ['yes', '1', 'on', 'true', 1, true], true)) {
            return true;
        }
        if (\in_array($this->data[$key], ['no', '0', 'off', 'false', 0, false], true)) {
            return false;
        }

        return $default;
    }

    public function deserialize(string $class): object
    {
        $reflection = new \ReflectionClass($class);
        $instanceArgs = array_map(
            fn (\ReflectionParameter $parameter) => $this->data[StringCase::snake($parameter->getName())],
            $reflection->getConstructor()?->getParameters() ?? [],
        );

        return $reflection->newInstanceArgs($instanceArgs);
    }
}
