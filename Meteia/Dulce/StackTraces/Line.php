<?php

declare(strict_types=1);

namespace Meteia\Dulce\StackTraces;

class Line
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var int
     */
    private $number;

    /**
     * @var bool
     */
    private $shouldHighlight;

    public function __construct(string $text, int $number, bool $shouldHighlight)
    {
        $this->text = $text;
        $this->number = $number;
        $this->shouldHighlight = $shouldHighlight;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function number(): int
    {
        return $this->number;
    }

    public function shouldHighlight(): bool
    {
        return $this->shouldHighlight;
    }
}
