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
                    'resolve' => static function ($root) {
                        \assert(\is_object($root));

                        return $root->node;
                    },
                ],
            ],
        ]);
    }
}
