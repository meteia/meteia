<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Component;
use Meteia\Html\Metadata;
use Meteia\Html\Node;
use Meteia\Html\Scripts;
use Meteia\Html\Stylesheets;

class Head implements Component
{
    public function __construct(
        public Title $title,
        public Metadata $metadata,
        public Stylesheets $stylesheets,
        public Scripts $scripts,
    ) {}

    #[\Override]
    public function render(): Node
    {
        return el('head', [], $this->title, $this->metadata, $this->scripts, $this->stylesheets);
    }
}
