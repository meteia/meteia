<?php

declare(strict_types=1);

namespace Meteia\DependencyInjection;

use Meteia\Performance\Timings;

class TimedContainer implements Container
{
    public function __construct(
        private readonly Timings $timings,
        private readonly Container $container,
    ) {}

    #[\Override]
    public function call($callable, array $parameters = []): mixed
    {
        return $this->timings->measure('di-call', fn() => $this->container->call($callable, $parameters));
    }

    #[\Override]
    public function get(string $id)
    {
        return $this->timings->measure('di-get-' . $id, fn() => $this->container->get($id));
    }

    #[\Override]
    public function has(string $id): bool
    {
        return $this->timings->measure('di-has-' . $id, fn() => $this->container->has($id));
    }

    #[\Override]
    public function set(string $id, mixed $value): void
    {
        $this->timings->measure('di-set-' . $id, fn() => $this->container->set($id, $value));
    }
}
