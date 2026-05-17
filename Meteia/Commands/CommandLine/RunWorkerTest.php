<?php

declare(strict_types=1);

namespace Meteia\Commands\CommandLine;

use Bunny\Channel;
use Bunny\Client;
use Meteia\AdvancedMessageQueuing\AmbientMessageScopeSource;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Bootstrap\ApplicationPublicDir;
use Meteia\Commands\Command;
use Meteia\Commands\CommandBus;
use Meteia\Commands\CommandInbox;
use Meteia\Commands\Commands;
use Meteia\DependencyInjection\Container;
use Meteia\DependencyInjection\ReflectionContainer;
use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\ProcessId;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;

/**
 * @internal
 */
final class RunWorkerTest extends TestCase
{
    public function testAppContainerSharesWorkerClientWithoutSharingWorkerChannel(): void
    {
        $path = new ApplicationPath('.');
        $namespace = new ApplicationNamespace('App');
        $publicDir = new ApplicationPublicDir('public');
        $client = new Client();
        $workerChannel = $this->createStub(Channel::class);
        $values = [
            ApplicationPath::class => $path,
            ApplicationNamespace::class => $namespace,
            ApplicationPublicDir::class => $publicDir,
            Client::class => $client,
            Channel::class => $workerChannel,
        ];

        $container = $this->createStub(Container::class);
        $container->method('get')->willReturnCallback(static function (string $id) use (&$values): mixed {
            return $values[$id] ?? throw new RuntimeException('Missing container value: ' . $id);
        });

        $worker = new RunWorker(
            $this->createStub(Commands::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(CommandInbox::class),
            $container,
        );

        $appContainerMethod = new ReflectionMethod(RunWorker::class, 'appContainer');
        /** @var ReflectionContainer $appContainer */
        $appContainer = $appContainerMethod->invoke($worker);

        static::assertInstanceOf(ReflectionContainer::class, $appContainer);
        $definitions = $appContainer->internals()['definitions'];

        static::assertSame($client, $definitions[Client::class] ?? null);
        static::assertNotSame($workerChannel, $definitions[Channel::class] ?? null);
    }

    public function testDrainDispatchesCommandInsideIncomingMessageScopeAndCompletesUnitOfWork(): void
    {
        $defaultScope = self::scope();
        $incomingScope = self::scope();
        $scopeSource = new AmbientMessageScopeSource($defaultScope);
        $completedScope = null;
        $dispatchedAmbientScope = null;
        $dispatchedContainerScope = null;
        $values = [
            AmbientMessageScopeSource::class => $scopeSource,
        ];

        $container = $this->createStub(Container::class);
        $container->method('get')->willReturnCallback(static function (string $id) use (&$values): mixed {
            return $values[$id] ?? throw new RuntimeException('Missing container value: ' . $id);
        });
        $container->method('set')->willReturnCallback(static function (string $id, mixed $value) use (&$values): void {
            $values[$id] = $value;
        });

        $commandBus = $this->createStub(CommandBus::class);
        $commandBus->method('dispatch')->willReturnCallback(
            static function () use ($scopeSource, $container, &$dispatchedAmbientScope, &$dispatchedContainerScope): void {
                $dispatchedAmbientScope = $scopeSource->current();
                /** @var MessageScope $containerScope */
                $containerScope = $container->get(MessageScope::class);
                $dispatchedContainerScope = $containerScope;
            },
        );
        $values[CommandBus::class] = $commandBus;

        $unitOfWork = $this->createStub(UnitOfWork::class);
        $unitOfWork->method('complete')->willReturnCallback(
            static function (MessageScope $scope) use (&$completedScope): void {
                $completedScope = $scope;
            },
        );
        $values[UnitOfWork::class] = $unitOfWork;

        $worker = new RunWorker(
            $this->createStub(Commands::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(CommandInbox::class),
            $this->createStub(Container::class),
        );
        $appContainer = new ReflectionProperty(RunWorker::class, 'appContainer');
        $appContainer->setValue($worker, $container);

        $worker->drain($this->createStub(Command::class), $incomingScope);

        static::assertSame($incomingScope, $dispatchedAmbientScope);
        static::assertSame($incomingScope, $dispatchedContainerScope);
        static::assertSame($incomingScope, $completedScope);
        static::assertSame($defaultScope, $scopeSource->current());
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
