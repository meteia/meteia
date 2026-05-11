<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use Exception;

trait DeferredResolve
{
    /** @var array<string, mixed> */
    private array $fetched = [];

    /** @var list<string> */
    private array $deferred = [];

    /** @var array<string, mixed> */
    private array $mergedArgs = [];

    public function clearDeferred(): void
    {
        $this->deferred = [];
        $this->mergedArgs = [];
    }

    /**
     * @param array<string, mixed> $args
     */
    public function defer(string|\Stringable $id, array $args = []): void
    {
        $id = (string) $id;
        if (isset($this->fetched[$id])) {
            return;
        }

        $this->mergedArgs = array_merge_recursive($this->mergedArgs, $args);
        $this->deferred[] = $id;
    }

    public function fetch(string|\Stringable $id): mixed
    {
        $id = (string) $id;
        if (isset($this->fetched[$id])) {
            return $this->fetched[$id];
        }

        $this->fetched = array_replace($this->fetched, $this->load());

        return $this->fetched[$id] ?? null;
    }

    protected function store(string $id, mixed $type): void
    {
        $this->fetched[$id] = $type;
    }

    /**
     * @return list<string>
     */
    protected function deferredIds(): array
    {
        return array_values(array_unique($this->deferred));
    }

    /**
     * @return list<mixed>|null
     */
    protected function deferredArgument(string $string): ?array
    {
        if (!isset($this->mergedArgs[$string])) {
            return null;
        }

        $value = $this->mergedArgs[$string];

        return \is_array($value) ? array_values(array_unique($value)) : [$value];
    }

    /**
     * @return array<string, mixed>
     */
    private function load(): array
    {
        throw new Exception('load() must be implemented by the using class');
    }
}
