<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Management;

interface BindingResult
{
    public function accepted(): bool;

    public function describe(): string;
}
