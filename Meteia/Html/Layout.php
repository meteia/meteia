<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Bluestone\Contracts\Renderable;

interface Layout extends Renderable, HasHead, HasBody
{
}
