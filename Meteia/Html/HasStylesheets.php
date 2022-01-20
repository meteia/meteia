<?php

declare(strict_types=1);

namespace Meteia\Html;

interface HasStylesheets
{
    public function stylesheets(): Stylesheets;
}
