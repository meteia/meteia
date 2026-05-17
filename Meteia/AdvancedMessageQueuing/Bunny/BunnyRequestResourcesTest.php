<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Client;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class BunnyRequestResourcesTest extends TestCase
{
    public function testReleaseDisconnectsAnOpenClient(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('canDisconnect')->willReturn(true);
        $client->expects($this->once())->method('disconnect');

        (new BunnyRequestResources($client))->release();
    }

    public function testReleaseLeavesAClosedClientAlone(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('canDisconnect')->willReturn(false);
        $client->expects($this->never())->method('disconnect');

        (new BunnyRequestResources($client))->release();
    }
}
