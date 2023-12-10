<?php

declare(strict_types=1);

namespace Meteia\DependencyInjection;

class ReflectionContainer implements Container
{
    private array $cache = [];

    public function __construct(
        private array $definitions = [],
    ) {
        $this->definitions[Container::class] = $this;
    }

    public function call($callable, array $parameters = []): mixed
    {
        if (\is_array($callable) && \is_object($callable[0])) {
            return $this->resolveCallable($callable, $parameters);
        }

        return $callable(...$this->resolveFunctionParameters($callable, $parameters));
    }

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

        if (class_exists($target)) {
            return $this->save($id, $this->resolveClass($target));
        }

        throw new \Exception("{$id} was not resolvable, and {$target} was not a class?");
    }

    public function has(string $id): bool
    {
        return $this->get($id) !== null;
    }

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

        throw new \Exception("Uncertain what to do with this... {$id}, {$value}");
    }

    public function internals(): array
    {
        return ['cache' => $this->cache, 'definitions' => $this->definitions];
    }

    private function resolveCallable(callable $callable, array $parameters = []): mixed
    {
        if (\is_array($callable)) {
            $rm = new \ReflectionMethod($callable[0], $callable[1]);
        } else {
            $rm = new \ReflectionMethod($callable, '__invoke');
        }
        $resolved = $this->resolveMethodParameters($rm, $parameters);

        $resolved = $callable(...$resolved);
        if (\is_string($resolved) && class_exists($resolved)) {
            $resolved = $this->get($resolved);
        }

        return $resolved;
    }

    private function resolveClass(string $className): mixed
    {
        $r = new \ReflectionClass($className);
        if (!$r->isInstantiable()) {
            return null;
        }

        $rc = $r->getConstructor();
        if (!$rc) {
            return new $className();
        }
        $resolved = $this->resolveMethodParameters($rc);
        $instance = new $className(...$resolved);
        $this->cache[$className] = $instance;

        return $instance;
    }

    private function resolveFunctionParameters(callable $callable, array $parameters = []): array
    {
        $rm = new \ReflectionFunction($callable);

        return array_map(fn ($param) => $this->resolveParameter($param, $parameters), $rm->getParameters());
    }

    private function resolveMethodParameters(\ReflectionMethod $method, array $parameters = []): array
    {
        return array_map(fn ($param) => $this->resolveParameter($param, $parameters), $method->getParameters());
    }

    private function resolveParameter(\ReflectionParameter $rp, array $parameters = []): mixed
    {
        if (isset($parameters[$rp->getName()])) {
            return $parameters[$rp->getName()];
        }

        $expectedType = $rp->getType();
        if ($expectedType === null) {
            throw new \Exception('Missing Type? ' . $rp->getName());
        }

        if ($rp->isDefaultValueAvailable()) {
            return $rp->getDefaultValue();
        }
        if ($expectedType->allowsNull() || $expectedType->isBuiltin()) {
            return null;
        }

        if ($expectedType instanceof \ReflectionNamedType) {
            return $this->get($expectedType->getName());
        }

        throw new \Exception('Unsupported Type');
    }

    private function save(string $id, mixed $resolved): mixed
    {
        $this->cache[$id] = $resolved;

        return $resolved;
    }
}
