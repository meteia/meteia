<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Client;
use Meteia\Bootstrap\RequestResources;
use Override;
use Throwable;

final readonly class BunnyRequestResources implements RequestResources
{
    public function __construct(
        private Client $client,
    ) {}

    #[Override]
    public function release(): void
    {
        try {
            if ($this->client->canDisconnect()) {
                $this->client->disconnect();
            }
        } catch (Throwable) {
            return;
        }
    }
}
