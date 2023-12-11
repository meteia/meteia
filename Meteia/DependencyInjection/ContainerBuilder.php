<?php

declare(strict_types=1);

namespace Meteia\DependencyInjection;

use Meteia\Application\ApplicationNamespace;
use Meteia\Application\ApplicationPath;
use Meteia\ValueObjects\Identity\FilesystemPath;

abstract class ContainerBuilder
{
    public static function build(
        ApplicationPath $applicationPath,
        ApplicationNamespace $applicationNamespace,
        array $additionalDefinitions = [],
    ): Container {
        $defaults = Definitions::glob(new FilesystemPath(__DIR__, '..', '*', 'DependencyInjection.php'));
        $application = Definitions::glob($applicationPath->join($applicationNamespace, '*', 'DependencyInjection.php'));

        return new ReflectionContainer([...$defaults, ...$application, ...$additionalDefinitions]);
    }
}
