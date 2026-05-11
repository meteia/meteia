<?php

declare(strict_types=1);

namespace Meteia\GraphQL\Types;

use DateTimeZone;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use Meteia\GraphQL\Contracts\RequestContext;
use Meteia\GraphQL\Contracts\Resolver;
use Override;

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

    #[Override]
    public function data($root, array $args, RequestContext $requestContext): string
    {
        \assert(\is_string($root));
        $date = new \DateTime($root, new DateTimeZone('UTC'));
        $format = $args['format'] ?? \DateTime::ATOM;
        \assert(\is_string($format));

        return $date->format($format);
    }

    #[Override]
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        if ($valueNode instanceof StringValueNode) {
            return $valueNode->value;
        }

        return null;
    }

    #[Override]
    public function parseValue($value)
    {
        \assert(\is_string($value));
        $date = new \DateTime($value, new DateTimeZone('UTC'));

        return $date->format(\DateTime::ATOM);
    }

    #[Override]
    public function serialize($value)
    {
        return $value;
    }
}
