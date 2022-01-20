<?php

declare(strict_types=1);

namespace Meteia\CommandLine;

use DI\Container;
use Invoker\InvokerInterface;
use Meteia\Application\ApplicationNamespace;
use Meteia\Application\ApplicationPath;
use Meteia\Classy\ClassesImplementing;
use Meteia\Classy\PsrClasses;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Commands implements \IteratorAggregate
{
    /**
     * @param Container|InvokerInterface $container
     */
    public function __construct(
        private Container $container,
        private ApplicationNamespace $applicationNamespace,
        private ApplicationPath $applicationPath,
    ) {
    }

    public function getIterator(): \Generator
    {
        $glob = ['*', 'CommandLine', '*.php'];
        $classes = new PsrClasses($this->applicationPath, (string) $this->applicationNamespace, $glob);
        $commandClassnames = new ClassesImplementing($classes, Command::class);
        foreach ($commandClassnames as $commandClassname) {
            $commandName = $this->commandName($commandClassname);
            $command = new \Symfony\Component\Console\Command\Command($commandName);
            $command->setDefinition($this->inputDefinition($commandClassname));
            $command->setCode(
                function (InputInterface $input, OutputInterface $output) use ($commandClassname) {
                    $this->container->set(InputInterface::class, $input);
                    $this->container->set(OutputInterface::class, $output);
                    $command = $this->container->get($commandClassname);
                    if (!method_exists($command, 'execute')) {
                        throw new \Exception('Command is missing required execute() method');
                    }
                    $this->container->call([$command, 'execute']);
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
            function ($part) {
                return !in_array($part, ['CommandLine', 'Meteia', (string) $this->applicationNamespace], true);
            },
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
