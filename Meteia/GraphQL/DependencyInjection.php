<?php

declare(strict_types=1);

use Meteia\Application\ApplicationNamespace;
use Meteia\Application\ApplicationPath;
use Meteia\Classy\ClassesImplementing;
use Meteia\Classy\PsrClasses;
use Meteia\DependencyInjection\Container;
use Meteia\GraphQL\Contracts\Field;
use Meteia\GraphQL\SchemaFields;

return [
    SchemaFields::class => function (ApplicationPath $applicationPath, ApplicationNamespace $namespace, Container $container): SchemaFields {
        $classes = new PsrClasses($applicationPath, (string) $namespace, $applicationPath->find('GraphQL', '.*\.php'));
        $classes = new ClassesImplementing($classes, Field::class);

        return new SchemaFields($container, $classes);
    },
];
