<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Exception;
use Meteia\Commands\Exceptions\UnknownCommandHandler;
use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\Domain\PendingCommands;
use Meteia\EventSourcing\PendingEvents;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Meteia\ValueObjects\Identity\ProcessId;
use Override;
use PHPUnit\Framework\TestCase;

final class InProcessCommandExecutionTest extends TestCase
{
    public function testExecuteReturnsHandlerResultAfterCompletingUnitOfWork(): void
    {
        $scope = self::scope();
        $completedScope = null;
        $execution = new InProcessCommandExecution(
            self::handlers(new Fixtures\ReturningResultCommandHandler()),
            self::unitOfWork($completedScope),
            self::scopeSource($scope),
        );

        static::assertSame('handled', $execution->execute(new Fixtures\ResultCommand()));
        static::assertSame($scope, $completedScope);
    }

    public function testExecuteDoesNotCompleteUnitOfWorkWhenHandlerFails(): void
    {
        $completedScope = null;
        $execution = new InProcessCommandExecution(
            self::handlers(new Fixtures\FailingResultCommandHandler()),
            self::unitOfWork($completedScope),
            self::scopeSource(self::scope()),
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('boom');

        try {
            $execution->execute(new Fixtures\ResultCommand());
        } finally {
            static::assertNull($completedScope);
        }
    }

    public function testExecutePropagatesHandlerResolutionErrors(): void
    {
        $completedScope = null;
        $execution = new InProcessCommandExecution(
            new class implements CommandHandlers {
                #[Override]
                public function handlerFor(Command $command): CommandHandler
                {
                    throw new UnknownCommandHandler($command::class);
                }
            },
            self::unitOfWork($completedScope),
            self::scopeSource(self::scope()),
        );

        $this->expectException(UnknownCommandHandler::class);

        try {
            $execution->execute(new Fixtures\ResultCommand());
        } finally {
            static::assertNull($completedScope);
        }
    }

    /**
     * @param CommandHandler<Fixtures\ResultCommand, string> $handler
     */
    private static function handlers(CommandHandler $handler): CommandHandlers
    {
        return new readonly class($handler) implements CommandHandlers {
            /**
             * @param CommandHandler<Fixtures\ResultCommand, string> $handler
             */
            public function __construct(
                private CommandHandler $handler,
            ) {}

            #[Override]
            public function handlerFor(Command $command): CommandHandler
            {
                return $this->handler;
            }
        };
    }

    private static function unitOfWork(?MessageScope &$completedScope): UnitOfWork
    {
        return new class($completedScope) implements UnitOfWork {
            public function __construct(
                private ?MessageScope &$completedScope,
            ) {}

            #[Override]
            public function caused(PendingEvents $events): void {}

            #[Override]
            public function wantsTo(PendingCommands $commands): void {}

            #[Override]
            public function complete(MessageScope $scope): void
            {
                $this->completedScope = $scope;
            }
        };
    }

    private static function scopeSource(MessageScope $scope): MessageScopeSource
    {
        return new readonly class($scope) implements MessageScopeSource {
            public function __construct(
                private MessageScope $scope,
            ) {}

            #[Override]
            public function current(): MessageScope
            {
                return $this->scope;
            }
        };
    }

    private static function scope(): MessageScope
    {
        return new MessageScope(CorrelationId::random(), CausationId::random(), ProcessId::random());
    }
}
