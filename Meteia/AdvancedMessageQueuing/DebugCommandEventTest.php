<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing;

use Bunny\Channel;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\CommandLine\PayloadParser;
use Meteia\Commands\Accepted;
use Meteia\Commands\Command as DomainCommand;
use Meteia\Debug\Commands\Ping;
use Meteia\Debug\CommandSinks\Ping as PingEndpoint;
use Meteia\DependencyInjection\Container;
use Meteia\DependencyInjection\ContainerBuilder;
use Meteia\Events\EventOutbox;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class DebugCommandEventTest extends TestCase
{
    public function testPayloadParserResolvesCommandsDebugPing(): void
    {
        $parser = new PayloadParser();
        $namespace = new ApplicationNamespace('Meteia');

        $fqcn = $parser->resolve('Debug.Commands.Ping', $namespace, DomainCommand::class);

        DebugCommandEventTest::assertSame(Ping::class, $fqcn);
    }

    public function testPingDenormalizesWithReplyTo(): void
    {
        $serializer = $this->getContainer()->get(SerializerInterface::class);

        $ping = $serializer->denormalize(['replyTo' => 'amq.gen-test-123'], Ping::class);

        DebugCommandEventTest::assertInstanceOf(Ping::class, $ping);
        DebugCommandEventTest::assertSame('amq.gen-test-123', $ping->replyTo);
    }

    public function testPingEndpointPublishesReplyDirectlyWhenReplyToPresent(): void
    {
        $channel = $this->createMock(Channel::class);
        $channel->expects($this->once())->method('publish');

        $eventOutbox = $this->createMock(EventOutbox::class);
        $eventOutbox->expects($this->once())->method('publish');

        $serializer = $this->getContainer()->get(SerializerInterface::class);
        $logger = $this->createStub(LoggerInterface::class);

        $endpoint = new PingEndpoint($channel, $serializer, $eventOutbox, $logger);

        $pingWithReply = new Ping(replyTo: 'amq.gen-test-123');
        $result = $endpoint->handle($pingWithReply);

        DebugCommandEventTest::assertInstanceOf(Accepted::class, $result);
    }

    private function getContainer(): Container
    {
        // Minimal container for serializer (reuses the framework DI)
        static $container = null;

        if ($container === null) {
            $container = ContainerBuilder::build(new ApplicationPath('.'), new ApplicationNamespace('Meteia'), []);
        }

        return $container;
    }
}
