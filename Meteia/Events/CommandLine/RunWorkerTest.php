<?php

declare(strict_types=1);

namespace Meteia\Events\CommandLine;

use ArrayIterator;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\DependencyInjection\Container;
use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\EventSourcing\Fixtures\AggregateRecorded;
use Meteia\Events\EventInbox;
use Meteia\Events\EventSink;
use Meteia\Events\EventSinks;
use Meteia\Events\PublishedEvent;
use Meteia\EventSourcing\StreamId;
use Meteia\EventSourcing\StreamVersion;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\ProcessId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @internal
 */
final class RunWorkerTest extends TestCase
{
    public function testRegistersEventSinksThatCompleteTheUnitOfWorkAfterDraining(): void
    {
        $registeredSink = null;
        $drainedScope = null;
        $completedScope = null;

        $eventInbox = $this->createStub(EventInbox::class);
        $eventInbox->method('subscribe')->willReturnCallback(
            static function (string $eventClassName, string $sinkClassName, EventSink $sink) use (&$registeredSink): void {
                \assert($eventClassName === AggregateRecorded::class, 'event worker must subscribe the configured event');
                \assert($sinkClassName === EventSink::class, 'event worker must subscribe the configured sink class');
                $registeredSink = $sink;
            },
        );
        $eventSinks = $this->createStub(EventSinks::class);
        $eventSinks->method('getIterator')->willReturn(new ArrayIterator([
            AggregateRecorded::class => [EventSink::class],
        ]));
        $innerSink = $this->createStub(EventSink::class);
        $innerSink->method('drain')->willReturnCallback(
            static function (PublishedEvent $_event, MessageScope $scope) use (&$drainedScope): void {
                $drainedScope = $scope;
            },
        );
        $unitOfWork = $this->createStub(UnitOfWork::class);
        $unitOfWork->method('complete')->willReturnCallback(
            static function (MessageScope $scope) use (&$completedScope): void {
                $completedScope = $scope;
            },
        );
        $container = $this->createStub(Container::class);
        $container->method('get')->willReturnCallback(
            static fn(string $id): mixed => match ($id) {
                EventSink::class => $innerSink,
                UnitOfWork::class => $unitOfWork,
                default => throw new RuntimeException('Unexpected container value: ' . $id),
            },
        );
        $input = $this->createStub(InputInterface::class);
        $input->method('getOption')->willReturnMap([
            ['only', null],
            ['once', true],
        ]);

        (new RunWorker(
            $eventInbox,
            $this->createStub(LoggerInterface::class),
            $container,
            $eventSinks,
            $input,
            new ApplicationNamespace('App'),
        ))->execute();

        $scope = self::scope();
        \assert($registeredSink instanceof EventSink, 'event worker must register an event sink');
        $registeredSink->drain(self::publishedEvent(), $scope);

        static::assertSame($scope, $drainedScope);
        static::assertSame($scope, $completedScope);
    }

    private static function publishedEvent(): PublishedEvent
    {
        return PublishedEvent::fromMessage(
            StreamId::random(),
            StreamVersion::start(),
            new AggregateRecorded(),
            CausationId::random(),
            CorrelationId::random(),
            new DateTimeImmutable(),
        );
    }

    private static function scope(): MessageScope
    {
        return new MessageScope(
            CorrelationId::random(),
            CausationId::random(),
            ProcessId::random(),
        );
    }
}
