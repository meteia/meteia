<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

interface MessageScopeSource
{
    public function current(): MessageScope;
}
