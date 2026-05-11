<?php

declare(strict_types=1);

namespace Meteia\GraphQL\Types;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NullValueNode;
use GraphQL\Type\Definition\LeafType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils\Utils;
use Override;

/**
 * TODO: Where do i have a use for this?
 */
class NullType extends Type implements OutputType, LeafType
{
    public string $name;

    /**
     * @param array{name?: string} $config
     */
    public function __construct(array $config = [])
    {
        $this->name = $config['name'] ?? 'Null';
        Utils::assertValidName($this->name);
    }

    public function isValidLiteral(Node $valueNode): bool
    {
        return $valueNode instanceof NullValueNode;
    }

    public function isValidValue(mixed $value): bool
    {
        return $value === null;
    }

    #[Override]
    public function parseLiteral(Node $valueNode, ?array $variables = null): mixed
    {
        return null;
    }

    #[Override]
    public function parseValue(mixed $value): mixed
    {
        return null;
    }

    #[Override]
    public function serialize(mixed $value): mixed
    {
        return $value;
    }

    #[Override]
    public function toString(): string
    {
        return $this->name;
    }
}
