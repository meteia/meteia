<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\PsrConvention\Commands\ExamplePsrCommand;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PsrCommandsTest extends TestCase
{
    public function testDiscoversCommandsByPsrConvention(): void
    {
        $commands = new PsrCommands(new ApplicationPath('.'), new ApplicationNamespace('Meteia'));

        static::assertContains(ExamplePsrCommand::class, iterator_to_array($commands));
    }
}
