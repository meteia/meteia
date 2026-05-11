<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use Generator;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\Type;
use Meteia\Classy\Classes;
use Meteia\Classy\ClassesImplementing;
use Meteia\DependencyInjection\Container;
use Meteia\GraphQL\Contracts\QueryField;

final readonly class SchemaFields
{
    public function __construct(
        private Container $container,
        private Classes $fieldClasses,
    ) {}

    public function implementing(string $interface): Generator
    {
        $classes = new ClassesImplementing($this->fieldClasses, $interface);
        foreach ($classes as $queryFieldClassName) {
            /** @var QueryField&NullableType&Type $field */
            $field = $this->container->get($queryFieldClassName);

            yield $this->fieldName($queryFieldClassName) => [
                'type' => Type::nonNull($field),
                'args' => method_exists($field, 'args') ? $field->args() : [],
                // 'resolve' => function ($value, $args, RequestContext $context) use ($queryFieldClassName) {
                //    /** @var ObjectResolver $resolver */
                //    $resolver = $this->container->get($queryFieldClassName . 'Resolver');
                //    return $resolver->data($value, $args, $context);
                // },
            ];
        }
    }

    private function fieldName(string $queryFieldClassName): string
    {
        $names = explode('\\', $queryFieldClassName);

        return lcfirst(array_pop($names));
    }
}
