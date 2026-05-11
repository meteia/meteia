<?php

declare(strict_types=1);

namespace Meteia\CommandLine;

use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Application;

final readonly class ConsoleKernel
{
    public function __construct(
        private ApplicationNamespace $namespace,
        private ApplicationPath $path,
        private ApplicationPath $frameworkPath,
    ) {}

    public function run(): int
    {
        $initDefs = [
            ApplicationNamespace::class => $this->namespace,
            ApplicationPath::class => $this->path,
        ];
        $container = ContainerBuilder::build($this->path, $this->namespace, $initDefs);

        $appCommands = new Commands($container, $this->namespace, $this->path);
        $frameworkCommands = new Commands($container, new ApplicationNamespace('Meteia'), $this->frameworkPath);

        $app = new Application();
        foreach ($appCommands as $command) {
            $app->addCommand($command);
        }
        foreach ($frameworkCommands as $command) {
            $app->addCommand($command);
        }

        return $app->run();
    }
}
