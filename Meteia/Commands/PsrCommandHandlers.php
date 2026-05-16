<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\Commands\Exceptions\UnknownCommandHandler;
use Meteia\DependencyInjection\Container;
use Override;

/**
 * Resolves command handlers by PSR-4 convention.
 *
 * Default convention swaps `\Commands\Foo` in the command class name with
 * `\CommandHandlers\Foo`. The container is used only to construct the
 * resolved class.
 */
final readonly class PsrCommandHandlers implements CommandHandlers
{
    public function __construct(
        private Container $container,
    ) {}

    #[Override]
    public function handlerFor(Command $command): CommandHandler
    {
        $handlerClass = $this->handlerClassFor($command::class);
        if (!class_exists($handlerClass)) {
            throw new UnknownCommandHandler($command::class);
        }

        /** @var mixed $handler */
        $handler = $this->container->get($handlerClass);
        if (!$handler instanceof CommandHandler) {
            throw new UnknownCommandHandler($command::class);
        }

        return $handler;
    }

    /**
     * @param class-string<Command> $commandClass
     */
    private function handlerClassFor(string $commandClass): string
    {
        $segments = explode('\\Commands\\', $commandClass, 2);
        if (\count($segments) !== 2) {
            return $commandClass;
        }

        $handlerName = $segments[1] ?? null;
        if ($handlerName === null) {
            return $commandClass;
        }

        return $segments[0] . '\\CommandHandlers\\' . $handlerName;
    }
}
