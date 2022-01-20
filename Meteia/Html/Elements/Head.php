<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Bluestone\PhpTemplate;
use Meteia\Html\Metadata;
use Meteia\Html\Scripts;
use Meteia\Html\Stylesheets;

class Head
{
    use PhpTemplate;

    public function __construct(
        public Title $title,
        public Metadata $metadata,
        public Stylesheets $stylesheets,
        public Scripts $scripts,
    ) {
    }
}
