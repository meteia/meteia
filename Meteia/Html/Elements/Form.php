<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Stringable;

class Form implements Stringable
{
    public function __construct(
        private readonly string $action,
        private readonly string $method,
        private readonly Stringable $content,
    ) {
    }

    public function __toString(): string
    {
        return <<<EOF
            <form action="$this->action" method="$this->method">
                $this->content
            </form>
            EOF;
    }
}
