<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing;

use Bunny\Channel;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\CommandLine\PayloadParser;
use Meteia\Commands\Command as DomainCommand;
use Meteia\Debug\Commands\Ping;
use Meteia\Debug\CommandHandlers\Ping as PingHandler;
use Meteia\DependencyInjection\Container;
use Meteia\DependencyInjection\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
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
        \assert($serializer instanceof SerializerInterface, 'test container must provide the serializer');
        \assert($serializer instanceof DenormalizerInterface, 'test container serializer must denormalize payloads');

        $ping = $serializer->denormalize(['replyTo' => 'amq.gen-test-123'], Ping::class);

        DebugCommandEventTest::assertInstanceOf(Ping::class, $ping);
        DebugCommandEventTest::assertSame('amq.gen-test-123', $ping->replyTo);
    }

    public function testPingHandlerPublishesReplyDirectlyWhenReplyToPresent(): void
    {
        $channel = $this->createMock(Channel::class);
        $channel->expects($this->once())->method('publish');

        $serializer = $this->getContainer()->get(SerializerInterface::class);
        \assert($serializer instanceof SerializerInterface, 'test container must provide the serializer');
        $logger = $this->createStub(LoggerInterface::class);

        $handler = new PingHandler($channel, $serializer, $logger);

        $pingWithReply = new Ping(replyTo: 'amq.gen-test-123');
        $handler->handle($pingWithReply);
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
