<?php

declare(strict_types=1);

namespace Meteia\Commands;

interface CommandDeliveries
{
    public function publishDelivery(CommandDelivery $delivery): void;
}
