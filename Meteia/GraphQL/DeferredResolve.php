<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

trait DeferredResolve
{
    /** @var array */
    private $fetched = [];

    /** @var string[] */
    private $deferred = [];

    /** @var string[] */
    private $mergedArgs = [];

    public function clearDeferred(): void
    {
        $this->deferred = [];
        $this->mergedArgs = [];
    }

    public function defer($id, $args = []): void
    {
        $id = (string) $id;
        if (isset($this->fetched[$id])) {
            return;
        }

        $this->mergedArgs = array_merge_recursive($this->mergedArgs, $args);
        $this->deferred[] = $id;
    }

    public function fetch($id)
    {
        $id = (string) $id;
        if (isset($this->fetched[$id])) {
            return $this->fetched[$id];
        }

        $this->fetched = array_replace($this->fetched, $this->load());

        return $this->fetched[$id] ?? null;
    }

    protected function store($id, $type): void
    {
        $this->fetched[$id] = $type;
    }

    protected function deferredIds()
    {
        return array_values(array_unique($this->deferred));
    }

    protected function deferredArgument($string)
    {
        if (!isset($this->mergedArgs[$string])) {
            return null;
        }

        return \is_array($this->mergedArgs[$string])
            ? array_unique($this->mergedArgs[$string])
            : [$this->mergedArgs[$string]];
    }

    private function load(): array
    {
        throw new \Exception('load() must be implemented by the using class');
    }
}
