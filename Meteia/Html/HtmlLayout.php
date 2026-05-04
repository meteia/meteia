<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Html\Elements\Body;
use Meteia\Html\Elements\Head;

class HtmlLayout implements Layout
{
    public function __construct(
        private readonly Head $head,
        private readonly Body $body,
    ) {}

    #[\Override]
    public function __toString(): string
    {
        return <<<EOF
        <!DOCTYPE html>
        <html lang="en">
        {$this->head}
        {$this->body}
        </html>
        EOF;
    }

    #[\Override]
    public function body(): Body
    {
        return $this->body;
    }

    #[\Override]
    public function head(): Head
    {
        return $this->head;
    }
}
