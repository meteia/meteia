<?php

declare(strict_types=1);

namespace Meteia\Html;

use Stringable;

interface Layout extends Stringable, HasHead, HasBody
{
}
