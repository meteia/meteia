<?php

declare(strict_types=1);

namespace Meteia\Html;

interface Header extends \Stringable
{
    public function title($title): self;
}
