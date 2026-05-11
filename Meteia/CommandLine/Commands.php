<?php

declare(strict_types=1);

namespace Meteia\CommandLine;

use Exception;
use Generator;
use IteratorAggregate;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Classy\ClassesImplementing;
use Meteia\Classy\PsrClasses;
use Meteia\DependencyInjection\Container;
use Meteia\ErrorHandling\Endpoints\ConsoleErrorEndpoint;
use Override;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function Meteia\Http\Functions\send;

/**
 * @implements IteratorAggregate<array-key, SymfonyCommand>
 */
final readonly class Commands implements IteratorAggregate
{
    public function __construct(
        private Container $container,
        private ApplicationNamespace $applicationNamespace,
        private ApplicationPath $applicationPath,
    ) {}

    #[Override]
    public function getIterator(): Generator
    {
        $classes = new PsrClasses(
            $this->applicationPath,
            (string) $this->applicationNamespace,
            ['.+', 'CommandLine', '.*\.php'],
        );
        $commandClassnames = new ClassesImplementing($classes, Command::class);
        foreach ($commandClassnames as $commandClassname) {
            \assert(\is_string($commandClassname) && is_subclass_of($commandClassname, Command::class));
            $commandName = $this->commandName($commandClassname);
            $command = new SymfonyCommand($commandName);
            $command->setDefinition($commandClassname::inputDefinition());
            $command->setDescription($commandClassname::description());
            $command->setCode(function (InputInterface $input, OutputInterface $output) use ($commandClassname): void {
                try {
                    $this->container->set(InputInterface::class, $input);
                    $this->container->set(OutputInterface::class, $output);
                    $command = $this->container->get($commandClassname);
                    \assert(\is_object($command));
                    if (!method_exists($command, 'execute')) {
                        throw new Exception('Command is missing required execute() method');
                    }
                    $this->container->call([$command, 'execute']);
                } catch (Throwable $throwable) {
                    $this->container->set(Throwable::class, $throwable);
                    $errorEndpoint = $this->container->get(ConsoleErrorEndpoint::class);

                    /** @var ResponseInterface $response */
                    $response = $this->container->call([
                        $errorEndpoint,
                        'response',
                    ], [$throwable]);
                    send($response);
                }
            });

            yield $command;
        }
    }

    private function commandName(string $className): string
    {
        $commandNameParts = explode('\\', trim($className, '\\'));
        $commandNameParts = array_filter(
            $commandNameParts,
            fn($part) => !\in_array($part, ['CommandLine', 'Meteia', (string) $this->applicationNamespace], true),
        );

        return implode(':', $commandNameParts);
    }
}
