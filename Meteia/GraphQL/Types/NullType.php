<?php

declare(strict_types=1);

namespace Meteia\GraphQL\Types;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NullValueNode;
use GraphQL\Type\Definition\LeafType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils\Utils;

/**
 * TODO: Where do i have a use for this?
 */
class NullType extends Type implements OutputType, LeafType
{
    public function __construct(array $config = [])
    {
        $this->name = $config['name'] ?? $this->tryInferName();
        Utils::assertValidName($this->name);
    }

    /**
     * @param \GraphQL\Language\AST\Node $valueNode
     */
    public function isValidLiteral($valueNode)
    {
        return $valueNode instanceof NullValueNode;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public function isValidValue($value)
    {
        return $value === null;
    }

    /**
     * Parses an externally provided literal value (hardcoded in GraphQL query) to use as an input.
     */
    public function parseLiteral(Node $valueNode, array $variables = null)
    {
        return null;
    }

    /**
     * Parses an externally provided value (query variable) to use as an input.
     *
     * @param mixed $value
     */
    public function parseValue($value)
    {
        return null;
    }

    public function serialize($value)
    {
        return $value;
    }
}
