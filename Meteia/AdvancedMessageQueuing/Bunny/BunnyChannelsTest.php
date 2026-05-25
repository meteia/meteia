<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
final class BunnyChannelsTest extends TestCase
{
    public function testPublishingChannelIsReusedUntilReset(): void
    {
        $firstChannel = $this->createStub(Channel::class);
        $secondChannel = $this->createStub(Channel::class);
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(2))
            ->method('channel')
            ->willReturnOnConsecutiveCalls($firstChannel, $secondChannel);
        $client->expects($this->once())->method('canDisconnect')->willReturn(true);
        $client->expects($this->once())->method('disconnect');

        $channels = new BunnyChannels($client, $this->createStub(LoggerInterface::class));

        static::assertSame($firstChannel, $channels->publishingChannel());
        static::assertSame($firstChannel, $channels->publishingChannel());

        $channels->reset();

        static::assertSame($secondChannel, $channels->publishingChannel());
    }
}
