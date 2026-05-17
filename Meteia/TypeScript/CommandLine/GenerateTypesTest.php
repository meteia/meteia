<?php

declare(strict_types=1);

namespace Meteia\TypeScript\CommandLine;

use Meteia\Commands\Commands;
use Meteia\Events\Events;
use Meteia\TypeScript\Fixtures\CommandPayloads\ConfigureCookie;
use Meteia\TypeScript\Fixtures\CommandPayloads\RegisterStudent;
use Meteia\TypeScript\Fixtures\CommandPayloads\ScheduleLesson;
use Meteia\TypeScript\Fixtures\EventPayloads\StudentRegistered;
use Override;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Traversable;

/**
 * @internal
 */
final class GenerateTypesTest extends TestCase
{
    public function testWritesGeneratedTypesToStdoutByDefault(): void
    {
        $output = new BufferedOutput();
        $command = new GenerateTypes(
            new ArrayInput([], GenerateTypes::inputDefinition()),
            $output,
            $this->commands(),
            $this->events(),
        );

        $command->execute();

        static::assertStringContainsString('export type MeteiaCommandPayloads = {', $output->fetch());
    }

    public function testWritesGeneratedTypesToOutputFile(): void
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'meteia-ts-');
        $output = new BufferedOutput();
        $command = new GenerateTypes(
            new ArrayInput(['--output' => $path], GenerateTypes::inputDefinition()),
            $output,
            $this->commands(),
            $this->events(),
        );

        $command->execute();

        static::assertStringContainsString('export type MeteiaEventPayloads = {', (string) file_get_contents($path));
    }

    private function commands(): Commands
    {
        return new class implements Commands {
            #[Override]
            public function getIterator(): Traversable
            {
                yield RegisterStudent::class;
                yield ScheduleLesson::class;
                yield ConfigureCookie::class;
            }
        };
    }

    private function events(): Events
    {
        return new class implements Events {
            #[Override]
            public function getIterator(): Traversable
            {
                yield StudentRegistered::class;
            }
        };
    }
}
