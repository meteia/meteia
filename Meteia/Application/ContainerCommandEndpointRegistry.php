<?php

declare(strict_types=1);

namespace Meteia\Application;

use Meteia\Application\Exceptions\UnknownCommandEndpoint;
use Meteia\DependencyInjection\Container;
use Override;

/**
 * Resolves a `CommandEndpoint` by container lookup, using a configurable name-derivation rule.
 * Default convention swaps `\Commands\Foo` in the command class name with `\CommandSinks\Foo`.
 */
final readonly class ContainerCommandEndpointRegistry implements CommandEndpointRegistry
{
    public function __construct(
        private Container $container,
    ) {}

    #[Override]
    public function endpointFor(string $commandClass): CommandEndpoint
    {
        $endpointClass = $this->endpointClassFor($commandClass);
        if (!class_exists($endpointClass)) {
            throw new UnknownCommandEndpoint($commandClass);
        }

        $endpoint = $this->container->get($endpointClass);
        if (!$endpoint instanceof CommandEndpoint) {
            throw new UnknownCommandEndpoint($commandClass);
        }

        return $endpoint;
    }

    private function endpointClassFor(string $commandClass): string
    {
        $segments = explode('\\Commands\\', $commandClass, 2);
        if (\count($segments) !== 2) {
            return $commandClass;
        }

        return $segments[0] . '\\CommandSinks\\' . $segments[1];
    }
}
