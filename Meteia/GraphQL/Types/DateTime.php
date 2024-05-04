<?php

declare(strict_types=1);

namespace Meteia\GraphQL\Types;

use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use Meteia\GraphQL\Contracts\RequestContext;
use Meteia\GraphQL\Contracts\Resolver;

class DateTime extends ScalarType implements Resolver
{
    public function args()
    {
        return [
            'format' => [
                'type' => Type::string(),
                'defaultValue' => \DateTime::ATOM,
            ],
        ];
    }

    public function data($root, array $args, RequestContext $requestContext): string
    {
        $date = new \DateTime($root, new \DateTimeZone('UTC'));

        return $date->format($args['format']);
    }

    public function parseLiteral($valueNode, ?array $variables = null)
    {
        if ($valueNode instanceof StringValueNode) {
            return $valueNode->value;
        }

        return null;
    }

    public function parseValue($value)
    {
        $date = new \DateTime($value, new \DateTimeZone('UTC'));

        return $date->format(\DateTime::ATOM);
    }

    public function serialize($value)
    {
        return $value;
    }
}
