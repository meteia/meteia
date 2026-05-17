<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Management;

use Bunny\ChannelInterface;
use Bunny\Client;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class BunnyRabbitMqManagementTest extends TestCase
{
    public function testBindQueueToExchangeUsesExistingClientAndClosesOnlyTheTemporaryChannel(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel->expects($this->once())->method('exchangeDeclare');
        $channel->expects($this->once())->method('queueBind');
        $channel->expects($this->once())->method('close');

        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('channel')->willReturn($channel);
        $client->expects($this->never())->method('connect');
        $client->expects($this->never())->method('disconnect');

        $result = (new BunnyRabbitMqManagement($client))->bindQueueToExchange(
            new VHostName('/'),
            new QueueName('reply-queue'),
            new ExchangeName('events'),
            new RoutingKey('user.abc'),
        );

        static::assertTrue($result->accepted());
    }
}
