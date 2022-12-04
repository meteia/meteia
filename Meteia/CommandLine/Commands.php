<?php

declare(strict_types=1);

namespace Meteia\CommandLine;

use Exception;
use Generator;
use IteratorAggregate;
use Meteia\Application\ApplicationNamespace;
use Meteia\Application\ApplicationPath;
use Meteia\Classy\ClassesImplementing;
use Meteia\Classy\PsrClasses;
use Meteia\DependencyInjection\Container;
use Meteia\Dulce\Endpoints\ConsoleErrorEndpoint;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function Meteia\Http\Functions\send;

class Commands implements IteratorAggregate
{
    public function __construct(
        private readonly Container $container,
        private readonly ApplicationNamespace $applicationNamespace,
        private readonly ApplicationPath $applicationPath,
    ) {
    }

    public function getIterator(): Generator
    {
        $classes = new PsrClasses($this->applicationPath, (string) $this->applicationNamespace, ['.+', 'CommandLine', '.*\.php']);
        $commandClassnames = new ClassesImplementing($classes, Command::class);
        foreach ($commandClassnames as $commandClassname) {
            $commandName = $this->commandName($commandClassname);
            $command = new \Symfony\Component\Console\Command\Command($commandName);
            $command->setDefinition($commandClassname::inputDefinition());
            $command->setDescription($commandClassname::description());
            $command->setCode(
                function (InputInterface $input, OutputInterface $output) use ($commandClassname): void {
                    try {
                        $this->container->set(InputInterface::class, $input);
                        $this->container->set(OutputInterface::class, $output);
                        $command = $this->container->get($commandClassname);
                        if (!method_exists($command, 'execute')) {
                            throw new Exception('Command is missing required execute() method');
                        }
                        $this->container->call([$command, 'execute']);
                    } catch (Throwable $throwable) {
                        $this->container->set(Throwable::class, $throwable);
                        $errorEndpoint = $this->container->get(ConsoleErrorEndpoint::class);
                        /** @var ResponseInterface $response */
                        $response = $this->container->call([$errorEndpoint, 'response'], [$throwable]);
                        send($response);
                    }
                },
            );
            yield $command;
        }
    }

    private function commandName($className): string
    {
        $commandNameParts = explode('\\', trim($className, '\\'));
        $commandNameParts = array_filter(
            $commandNameParts,
            fn ($part) => !in_array($part, ['CommandLine', 'Meteia', (string) $this->applicationNamespace], true),
        );

        return implode(':', $commandNameParts);
    }

    private function inputDefinition(string $className): InputDefinition
    {
        $className .= 'InputDefinition';
        if (!class_exists($className)) {
            return new InputDefinition();
        }

        return $this->container->get($className);
    }
}
