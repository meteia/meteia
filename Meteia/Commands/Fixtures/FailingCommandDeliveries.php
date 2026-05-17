<?php

declare(strict_types=1);

namespace Meteia\Commands\Fixtures;

use Meteia\Commands\CommandDeliveries;
use Meteia\Commands\CommandDelivery;
use Override;
use RuntimeException;

final class FailingCommandDeliveries implements CommandDeliveries
{
    #[Override]
    public function publishDelivery(CommandDelivery $delivery): void
    {
        throw new RuntimeException('delivery failed');
    }
}
