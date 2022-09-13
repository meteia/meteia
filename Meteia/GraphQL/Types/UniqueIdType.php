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
        private readonly string $uniqueIdClass,
    ) {
        assert(is_subclass_of($uniqueIdClass, UniqueId::class), $uniqueIdClass . ' does not implement ' . UniqueId::class);
        parent::__construct([
            'name' => $this->classBasedName($uniqueIdClass),
        ]);
    }

    public function parseLiteral($valueNode, array $variables = null)
    {
        jdd($valueNode);
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
        jdd($value);
        try {
            return $this->uniqueIdClass::fromToken($value);
        } catch (\Throwable $t) {
            throw new InvalidScalarValue($t->getMessage());
        }
    }

    public function serialize($value)
    {
        return (string) $value;
    }

    public function data($root, array $args, RequestContext $requestContext): mixed
    {
        if (isset($root->id) && $root->id instanceof $this->uniqueIdClass) {
            return $root->id;
        }

        return new $this->uniqueIdClass($root->id ?? $args['id'] ?? $root);
    }
}
