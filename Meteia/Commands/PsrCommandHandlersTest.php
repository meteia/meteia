<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\DependencyInjection\ReflectionContainer;
use Meteia\PsrConvention\CommandHandlers\ExamplePsrCommand as ExamplePsrCommandHandler;
use Meteia\PsrConvention\Commands\ExamplePsrCommand;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PsrCommandHandlersTest extends TestCase
{
    public function testResolvesCommandToHandlerByPsrConvention(): void
    {
        $handlers = new PsrCommandHandlers(new ReflectionContainer());

        $handler = $handlers->handlerFor(new ExamplePsrCommand());

        static::assertInstanceOf(ExamplePsrCommandHandler::class, $handler);
    }
}
