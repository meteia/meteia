<?php

declare(strict_types=1);

namespace Meteia\Performance;

class Timings
{
    private float $childDurations = 0.0;

    private int $timeDepth = 0;

    /** @var array<string, float> */
    private array $timings = [];

    public function add(string $category, float $ms): void
    {
        $category = $this->filteredCategory($category);
        $this->timings[$category] = ($this->timings[$category] ?? 0.0) + $ms;
    }

    /**
     * @return array<string, float>
     */
    public function all(): array
    {
        $result = [];
        foreach ($this->timings as $k => $v) {
            $result[$k] = round($v, 4);
        }

        return $result;
    }

    /**
     * @template T
     * @param callable(): T $c
     * @return T
     */
    public function measure(string $category, callable $c): mixed
    {
        $category = $this->filteredCategory($category);
        ++$this->timeDepth;
        $startTime = hrtime(true);

        try {
            $result = $c();
        } finally {
            $endTime = hrtime(true);
            --$this->timeDepth;
            $duration = max(0.0, (($endTime - $startTime) / 1_000_000) - $this->childDurations);
            $this->childDurations += $duration;
            if ($this->timeDepth === 0) {
                $this->childDurations = 0.0;
            }

            $this->timings[$category] = ($this->timings[$category] ?? 0.0) + $duration;
        }

        return $result;
    }

    private function filteredCategory(string $category): string
    {
        $category = trim($category);

        return str_replace('\\', '.', $category);
    }
}
