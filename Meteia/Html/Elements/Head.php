<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Metadata;
use Meteia\Html\Scripts;
use Meteia\Html\Stylesheets;
use Stringable;

class Head implements Stringable
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
        return <<<EOF
            <head>
              $this->title
              $this->stylesheets
              $this->metadata
              $this->scripts
            </head>
            EOF;
    }
}
