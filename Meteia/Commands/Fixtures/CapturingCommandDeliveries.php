<?php

declare(strict_types=1);

namespace Meteia\Commands\Fixtures;

use Meteia\Commands\CommandDeliveries;
use Meteia\Commands\CommandDelivery;
use Override;

final class CapturingCommandDeliveries implements CommandDeliveries
{
    /** @var list<CommandDelivery> */
    public array $deliveries = [];

    #[Override]
    public function publishDelivery(CommandDelivery $delivery): void
    {
        $this->deliveries[] = $delivery;
    }
}
