<?php

declare(strict_types=1);

namespace Meteia\Html;

use Override;
use Stringable;

final readonly class TagName implements Stringable
{
    private const array VOID = [
        'area' => true,
        'base' => true,
        'br' => true,
        'col' => true,
        'embed' => true,
        'hr' => true,
        'img' => true,
        'input' => true,
        'link' => true,
        'meta' => true,
        'param' => true,
        'source' => true,
        'track' => true,
        'wbr' => true,
    ];

    private function __construct(
        public string $value,
    ) {}

    public static function of(string $name): self
    {
        return new self(strtolower($name));
    }

    public function isVoid(): bool
    {
        return isset(self::VOID[$this->value]);
    }

    #[Override]
    public function __toString(): string
    {
        return $this->value;
    }
}
