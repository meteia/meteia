<?php

declare(strict_types=1);

namespace Meteia\CommandLine;

use LogicException;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Database\CommandLine\Migrate;
use Meteia\DependencyInjection\Container;
use Meteia\ErrorHandling\Endpoints\ConsoleErrorEndpoint;
use Nyholm\Psr7\Response;
use PDOException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

/** @internal */
final class CommandsTest extends TestCase
{
    public function testCompletedMigrationReturnsSuccess(): void
    {
        $container = $this->createStub(Container::class);
        $container->method('get')->willReturn($this->createStub(Migrate::class));
        $container->method('call')->willReturn(null);

        self::assertSame(
            SymfonyCommand::SUCCESS,
            $this->migration($container)->run(new ArrayInput([]), new BufferedOutput()),
            'A completed migration reports success to the process supervisor.',
        );
    }

    public function testUnavailableDatabaseReturnsFailure(): void
    {
        $command = $this->migration($this->failing(new RuntimeException('Database not available')));

        self::assertSame(
            SymfonyCommand::FAILURE,
            $command->run(new ArrayInput([]), new BufferedOutput()),
            'Exhausted database availability retries must block application startup.',
        );
    }

    public function testFailedSqlReturnsFailure(): void
    {
        $command = $this->migration($this->failing(new PDOException('Migration SQL failed')));

        self::assertSame(
            SymfonyCommand::FAILURE,
            $command->run(new ArrayInput([]), new BufferedOutput()),
            'Migration SQL errors must not be reported as a successful deployment.',
        );
    }

    public function testFailureIsWrittenToConsoleOutput(): void
    {
        $output = new BufferedOutput();
        $this->migration($this->failing(new RuntimeException('Database not available')))->run(new ArrayInput([]), $output);

        self::assertSame(
            'Database not available',
            $output->fetch(),
            'The CLI retains its diagnostic without sending an HTTP response or clearing output buffers.',
        );
    }

    private function failing(Throwable $failure): Container
    {
        $migration = $this->createStub(Migrate::class);
        $endpoint = $this->createStub(ConsoleErrorEndpoint::class);
        $container = $this->createStub(Container::class);
        $container->method('get')->willReturnMap([
            [Migrate::class, $migration],
            [ConsoleErrorEndpoint::class, $endpoint],
        ]);
        $container->method('call')->willReturnCallback(static function (mixed $callable) use ($migration, $failure): mixed {
            if ($callable === [$migration, 'execute']) {
                throw $failure;
            }

            return new Response(500, [], $failure->getMessage());
        });

        return $container;
    }

    private function migration(Container $container): SymfonyCommand
    {
        $commands = new Commands($container, new ApplicationNamespace('Meteia'), new ApplicationPath(__DIR__, '../..'));
        foreach ($commands as $command) {
            if ($command->getName() === 'Database:Migrate') {
                return $command;
            }
        }

        throw new LogicException('Database:Migrate must be discoverable');
    }
}
