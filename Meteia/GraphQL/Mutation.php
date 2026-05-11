<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use Meteia\Classy\ClassBasedName;
use Meteia\GraphQL\Contracts\MutationField;

class Mutation extends ObjectType
{
    public function __construct(SchemaFields $schemaFields)
    {
        parent::__construct([
            'name' => (string) new ClassBasedName(static::class),
            'fields' => iterator_to_array($schemaFields->implementing(MutationField::class)),
        ]);
    }
}
