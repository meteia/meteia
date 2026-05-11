<?php

declare(strict_types=1);

namespace Meteia\Html;

use NoDiscard;
use Override;
use Stringable;

final readonly class Tag implements Node
{
    public function __construct(
        public TagName $name,
        public Attrs $attrs,
        public Children $children,
    ) {}

    #[NoDiscard]
    public function with(string|Stringable ...$more): self
    {
        return clone($this, ['children' => $this->children->append(...$more)]);
    }

    #[NoDiscard]
    public function withClass(ClassList|string $class): self
    {
        return clone($this, ['attrs' => $this->attrs->withClass($class)]);
    }

    #[NoDiscard]
    public function withAttr(string $name, bool|string|int|float|Stringable|null $value): self
    {
        return clone($this, ['attrs' => $this->attrs->with($name, $value)]);
    }

    #[Override]
    public function __toString(): string
    {
        return new HtmlEncoder()->encode($this);
    }
}
