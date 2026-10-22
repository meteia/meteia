<?php

declare(strict_types=1);

namespace Meteia\Classy;

use Meteia\TypeScript\CommandLine\GenerateTypes;
use Meteia\ValueObjects\Identity\FilesystemPath;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
final class PsrClassesTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testDiscoversRuntimeClassesWithoutAutoloadingColocatedTests(): void
    {
        $rejectTests = static function (string $class): void {
            if (str_ends_with($class, 'Test')) {
                throw new RuntimeException('Discovery must not autoload test classes: ' . $class);
            }
        };
        spl_autoload_register($rejectTests, true, true);

        try {
            $classes = new PsrClasses(
                new FilesystemPath(__DIR__, '..', '..'),
                'Meteia',
                ['TypeScript', 'CommandLine', '.*\\.php'],
            );

            static::assertSame([GenerateTypes::class], iterator_to_array($classes));
        } finally {
            spl_autoload_unregister($rejectTests);
        }
    }
}
