<?php

declare(strict_types=1);

use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Classy\ClassesImplementing;
use Meteia\Classy\MergedClasses;
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
        $regex = ['.+', 'GraphQL', '.+\.php'];

        $classes = new ClassesImplementing(
            new MergedClasses(
                new PsrClasses($meteiaPath, 'Meteia', $regex),
                new PsrClasses($applicationPath, (string) $namespace, $regex),
            ),
            Field::class,
        );

        return new SchemaFields($container, $classes);
    },
];
