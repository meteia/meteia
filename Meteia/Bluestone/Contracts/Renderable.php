<?php

declare(strict_types=1);

namespace Meteia\Bluestone\Contracts;

/**
 * @deprecated Use \Stringable instead
 */
interface Renderable extends \Stringable
{
    public function __toString();
}
