<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use Meteia\GraphQL\Contracts\QueryField;

class Query extends ObjectType
{
    use ClassBasedName;

    public function __construct(SchemaFields $schemaFields)
    {
        parent::__construct([
            'name' => $this->classBasedName(),
            'fields' => iterator_to_array($schemaFields->implementing(QueryField::class)),
        ]);
    }
}
