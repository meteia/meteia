<?php

declare(strict_types=1);

namespace Meteia\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use Meteia\GraphQL\ClassBasedName;

class PageInfo extends ObjectType
{
    use ClassBasedName;

    public function __construct()
    {
        parent::__construct([
            'name' => $this->classBasedName(),
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
