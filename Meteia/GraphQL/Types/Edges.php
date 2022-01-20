<?php

declare(strict_types=1);

namespace Meteia\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;

abstract class Edges extends ObjectType
{
    public function __construct(ObjectType $nodeType)
    {
        parent::__construct([
            'fields' => [
                'cursor' => [
                    'type' => self::nonNull(self::string()),
                ],
                'node' => [
                    'type' => self::nonNull($nodeType),
                    'resolve' => function ($root) {
                        // We've already resolved the 'nodeType', so pass through the node
                        // without hitting any possible 'nodeType' resolver.
                        return $root->node;
                    },
                ],
            ],
        ]);
    }
}
