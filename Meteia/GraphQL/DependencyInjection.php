<?php

declare(strict_types=1);

use Meteia\Application\ApplicationNamespace;
use Meteia\Application\ApplicationPath;
use Meteia\Classy\ClassesImplementing;
use Meteia\Classy\PsrClasses;
use Meteia\DependencyInjection\Container;
use Meteia\GraphQL\Contracts\Field;
use Meteia\GraphQL\SchemaFields;
use Meteia\ValueObjects\Identity\FilesystemPath;

return [
    SchemaFields::class => static function (
        ApplicationPath $applicationPath,
        ApplicationNamespace $namespace,
        Container $container,
    ): SchemaFields {
        $meteiaPath = new FilesystemPath(__DIR__, '..', '..')->realpath();
        $meteiaClasses = new PsrClasses($meteiaPath, 'Meteia', ['.+', 'GraphQL', '.+\.php']);

        $applicationClasses = new PsrClasses($applicationPath, (string) $namespace, ['.+', 'GraphQL', '.+\.php']);
        $classes = new ClassesImplementing([
            ...iterator_to_array($meteiaClasses),
            ...iterator_to_array($applicationClasses),
        ], Field::class);

        return new SchemaFields($container, $classes);
    },
];
