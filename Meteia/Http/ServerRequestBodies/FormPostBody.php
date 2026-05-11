<?php

declare(strict_types=1);

namespace Meteia\Http\ServerRequestBodies;

use Meteia\Library\StringCase;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionParameter;

readonly class FormPostBody implements ServerRequestBody
{
    /** @var array<array-key, mixed> */
    private array $data;

    public function __construct(ServerRequestInterface $request)
    {
        $parsed = $request->getParsedBody();
        $this->data = is_array($parsed) ? $parsed : [];
    }

    /**
     * @return array<array-key, mixed>
     */
    public function all(): array
    {
        return $this->data;
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

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public function deserialize(string $class): object
    {
        $reflection = new ReflectionClass($class);
        $instanceArgs = array_map(
            fn(ReflectionParameter $parameter) => $this->data[StringCase::snake($parameter->getName())] ?? null,
            $reflection->getConstructor()?->getParameters() ?? [],
        );

        $instance = $reflection->newInstanceArgs($instanceArgs);
        \assert($instance instanceof $class);

        return $instance;
    }
}
