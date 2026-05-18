<?php

declare(strict_types=1);

namespace Meteia\DependencyInjection;

use Closure;
use Exception;
use Override;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class ReflectionContainer implements Container
{
    private array $cache = [];

    public function __construct(
        private array $definitions = [],
    ) {
        $this->definitions[Container::class] = $this;
    }

    #[Override]
    public function call($callable, array $parameters = []): mixed
    {
        if (\is_array($callable) && isset($callable[0]) && \is_object($callable[0])) {
            /** @var callable $arrayCallable */
            $arrayCallable = $callable;

            return $this->resolveCallable($arrayCallable, $parameters);
        }

        \assert(\is_callable($callable));

        return $callable(...$this->resolveFunctionParameters($callable, $parameters));
    }

    #[Override]
    public function get(string $id): mixed
    {
        if (isset($this->cache[$id])) {
            return $this->cache[$id];
        }
        $target = $this->definitions[$id] ?? $id;

        if ($target instanceof $id) {
            return $target;
        }

        if (\is_callable($target)) {
            return $this->save($id, $this->resolveCallable($target));
        }

        if (\is_string($target) && class_exists($target)) {
            return $this->save($id, $this->resolveClass($target));
        }

        throw new Exception("{$id} was not resolvable");
    }

    #[Override]
    public function has(string $id): bool
    {
        $value = $this->get($id);

        return $value !== null;
    }

    #[Override]
    public function set(string $id, mixed $value): void
    {
        if (\is_callable($value)) {
            unset($this->cache[$id]);
            $this->definitions[$id] = $value;

            return;
        }

        if (\is_object($value)) {
            $this->cache[$id] = $value;

            return;
        }

        throw new Exception("Uncertain what to do with this... {$id}");
    }

    /**
     * @return array{cache: array<array-key, mixed>, definitions: array<array-key, mixed>}
     */
    public function internals(): array
    {
        return ['cache' => $this->cache, 'definitions' => $this->definitions];
    }

    /**
     * @param array<array-key, mixed> $parameters
     */
    private function resolveCallable(mixed $callable, array $parameters = []): mixed
    {
        $rm = null;
        if (\is_array($callable)) {
            \assert((\is_object($callable[0]) || \is_string($callable[0])) && \is_string($callable[1]));
            $rm = new ReflectionMethod($callable[0], $callable[1]);
        } elseif (\is_object($callable) && !$callable instanceof Closure) {
            $rm = new ReflectionMethod($callable, '__invoke');
        }

        \assert(\is_callable($callable));
        if ($rm === null) {
            $resolvedParams = $this->resolveFunctionParameters($callable, $parameters);
        } else {
            $resolvedParams = $this->resolveMethodParameters($rm, $parameters);
        }

        $resolved = $callable(...$resolvedParams);
        if (\is_string($resolved) && class_exists($resolved)) {
            return $this->get($resolved);
        }

        return $resolved;
    }

    /**
     * @param class-string $className
     */
    private function resolveClass(string $className): mixed
    {
        $r = new ReflectionClass($className);
        if (!$r->isInstantiable()) {
            return null;
        }

        $rc = $r->getConstructor();
        if (!$rc) {
            $instance = $r->newInstance();
            $this->cache[$className] = $instance;

            return $instance;
        }
        $resolved = $this->resolveMethodParameters($rc);
        $instance = $r->newInstanceArgs($resolved);
        $this->cache[$className] = $instance;

        return $instance;
    }

    /**
     * @param array<array-key, mixed> $parameters
     *
     * @return list<mixed>
     */
    private function resolveFunctionParameters(callable $callable, array $parameters = []): array
    {
        \assert($callable instanceof Closure || \is_string($callable));
        $rm = new ReflectionFunction($callable);

        return array_values(array_map(fn($param) => $this->resolveParameter(
            $param,
            $parameters,
        ), $rm->getParameters()));
    }

    /**
     * @param array<array-key, mixed> $parameters
     *
     * @return list<mixed>
     */
    private function resolveMethodParameters(ReflectionMethod $method, array $parameters = []): array
    {
        return array_values(array_map(fn($param) => $this->resolveParameter(
            $param,
            $parameters,
        ), $method->getParameters()));
    }

    /**
     * @param array<array-key, mixed> $parameters
     */
    private function resolveParameter(ReflectionParameter $rp, array $parameters = []): mixed
    {
        if (isset($parameters[$rp->getName()])) {
            return $parameters[$rp->getName()];
        }

        $expectedType = $rp->getType();
        if ($expectedType === null) {
            throw new Exception('Missing Type? ' . $rp->getName());
        }

        if ($rp->isDefaultValueAvailable()) {
            return $rp->getDefaultValue();
        }
        if ($expectedType instanceof ReflectionNamedType) {
            if ($expectedType->allowsNull() || $expectedType->isBuiltin()) {
                return null;
            }

            return $this->get($expectedType->getName());
        }
        if ($expectedType->allowsNull()) {
            return null;
        }

        throw new Exception('Unsupported Type');
    }

    private function save(string $id, mixed $resolved): mixed
    {
        $this->cache[$id] = $resolved;

        return $resolved;
    }
}
