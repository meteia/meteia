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
final class BunnyMessageLoopTest extends TestCase
{
    public function testOpensWorkerChannelsOnTheInjectedClient(): void
    {
        $firstChannel = $this->createStub(Channel::class);
        $secondChannel = $this->createStub(Channel::class);
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(2))
            ->method('channel')
            ->willReturnOnConsecutiveCalls($firstChannel, $secondChannel);
        $log = $this->createStub(LoggerInterface::class);

        $loop = new BunnyMessageLoop(new BunnyChannels($client, $log), $log);

        static::assertSame($firstChannel, $loop->channel());
        static::assertSame($secondChannel, $loop->channel());
    }

    public function testResetDisconnectsTheInjectedClientWhenItIsOpen(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('canDisconnect')->willReturn(true);
        $client->expects($this->once())->method('disconnect');
        $log = $this->createStub(LoggerInterface::class);

        (new BunnyMessageLoop(new BunnyChannels($client, $log), $log))->reset();
    }
}
