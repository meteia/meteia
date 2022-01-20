<?php

declare(strict_types=1);

namespace Meteia\GraphQL\Types;

use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Meteia\GraphQL\ClassBasedName;
use Meteia\GraphQL\ClientAwareErrors\InvalidScalarValue;
use Meteia\GraphQL\Contracts\RequestContext;
use Meteia\GraphQL\Contracts\Resolver;
use Meteia\ValueObjects\Identity\UniqueId;

class UniqueIdType extends ScalarType implements Resolver
{
    use ClassBasedName;

    public function __construct(
        private string $uniqueIdClass,
    ) {
        assert(is_subclass_of($uniqueIdClass, UniqueId::class), $uniqueIdClass . ' does not implement ' . UniqueId::class);
        parent::__construct([
            'name' => $this->classBasedName($uniqueIdClass),
        ]);
    }

    public function parseLiteral($valueNode, array $variables = null)
    {
        if ($valueNode instanceof StringValueNode) {
            // return $valueNode->value;
            try {
                $uid = $this->uniqueIdClass::fromToken($valueNode->value);
                // jdd(get_class($uid), $uid);
                return $uid;
            } catch (\Throwable $t) {
                throw new InvalidScalarValue($t->getMessage());
            }
        }
        jdd($valueNode);

        return null;
    }

    public function parseValue($value)
    {
        try {
            $uid = $this->uniqueIdClass::fromToken($value);
            // jdd(get_class($uid), $uid);
            return $uid;
        } catch (\Throwable $t) {
            throw new InvalidScalarValue($t->getMessage());
        }
    }

    public function serialize($value)
    {
        // FIXME: Why is $value a stdClass here?
        // jdd(get_class($value), $value);
        return $value->id ?? $value;

        jdd(get_class($value), $value);
        if ($value instanceof $this->uniqueIdClass) {
            jdd(get_class($value), $value);
        }
        // jdd($value);
        return $value->token();
        // return $value->token();
        // jdd($value);
        // return $value;
    }

    public function data($root, array $args, RequestContext $requestContext): mixed
    {
        // jdd($root, $args);
        return $root;
    }
}
