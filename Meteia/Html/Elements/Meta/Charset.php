<?php

declare(strict_types=1);

namespace Meteia\Html\Elements\Meta;

use Meteia\Html\Component;
use Meteia\Html\Node;

use function Meteia\Html\Elements\el;

readonly class Charset implements Component
{
    public function __construct(
        private string|\Stringable $characterSet = 'UTF-8',
    ) {}

    #[\Override]
    public function render(): Node
    {
        return el('meta', ['charset' => (string) $this->characterSet]);
    }
}
