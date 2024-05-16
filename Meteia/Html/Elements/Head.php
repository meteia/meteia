<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Metadata;
use Meteia\Html\Scripts;
use Meteia\Html\Stylesheets;

class Head implements \Stringable
{
    public function __construct(
        public Title $title,
        public Metadata $metadata,
        public Stylesheets $stylesheets,
        public Scripts $scripts,
    ) {
    }

    public function __toString(): string
    {
        return el('head', [], $this->title, $this->metadata, $this->stylesheets, $this->scripts);
    }
}
