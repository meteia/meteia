<?php

declare(strict_types=1);

namespace Meteia\Events\Debug;

use Meteia\Events\Event;

final readonly class Pong implements Event
{
    public function __construct() {}
}
