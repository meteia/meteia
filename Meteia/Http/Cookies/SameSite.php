<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

enum SameSite: string
{
    case Lax = 'Lax';
    case Strict = 'Strict';
    case None = 'None';
}
