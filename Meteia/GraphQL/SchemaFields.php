<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use Meteia\Classy\ClassesImplementing;
use Meteia\GraphQL\Contracts\ObjectResolver;
use Meteia\GraphQL\Contracts\QueryField;
use Meteia\GraphQL\Contracts\RequestContext;
use Psr\Container\ContainerInterface;

class SchemaFields
{
    public function __construct(
        private ContainerInterface $container,
        private iterable $fieldClasses,
    ) {
    }

    public function implementing(string $interface)
    {
        $classes = new ClassesImplementing($this->fieldClasses, $interface);
        foreach ($classes as $queryFieldClassName) {
            /** @var QueryField $field */
            $field = $this->container->get($queryFieldClassName);
            yield $this->fieldName($queryFieldClassName) => [
                'type' => $field,
                'args' => method_exists($field, 'args') ? $field->args() : [],
                // 'resolve' => function ($value, $args, RequestContext $context) use ($queryFieldClassName) {
                //    /** @var ObjectResolver $resolver */
                //    $resolver = $this->container->get($queryFieldClassName . 'Resolver');
                //
                //    return $resolver->data($value, $args, $context);
                // },
            ];
        }
    }

    private function fieldName(string $queryFieldClassName)
    {
        $names = explode('\\', $queryFieldClassName);

        return lcfirst(array_pop($names));
    }
}
