<?php

declare(strict_types=1);

namespace Meteia\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use Meteia\Classy\ClassBasedName;

class PageInfo extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => (string) new ClassBasedName(static::class),
            'fields' => [
                'startCursor' => [
                    'type' => self::string(),
                ],
                'endCursor' => [
                    'type' => self::string(),
                ],
                'hasNextPage' => [
                    'type' => self::nonNull(self::boolean()),
                ],
                'hasPreviousPage' => [
                    'type' => self::nonNull(self::boolean()),
                ],
            ],
        ]);
    }
}
