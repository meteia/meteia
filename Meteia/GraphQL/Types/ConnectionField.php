<?php

declare(strict_types=1);

namespace Meteia\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;

use function Meteia\Polyfills\array_map_assoc;

abstract class ConnectionField extends ObjectType
{
    public const ARG_AFTER = 'after';
    public const ARG_BEFORE = 'before';
    public const ARG_FIRST = 'first';
    public const ARG_LAST = 'last';

    public function __construct(PageInfo $pageInfo, Edges $edges)
    {
        parent::__construct([
            'fields' => [
                'id' => [
                    'type' => self::nonNull(self::string()),
                ],
                'edges' => [
                    'type' => self::nonNull(self::listOf(self::nonNull($edges))),
                ],
                'pageInfo' => [
                    'type' => self::nonNull($pageInfo),
                ],
            ],
        ]);
    }

    public function defaultArguments(): array
    {
        $args = array_map_assoc(static fn($key, $value) => [$key => $value['defaultValue'] ?? null], $this->argsWith());

        return array_filter($args);
    }

    public function argsWith(array $args = []): array
    {
        return array_replace($args, [
            self::ARG_FIRST => [
                'name' => self::ARG_FIRST,
                'description' => 'First N records',
                'type' => self::int(),
                'defaultValue' => 25,
            ],
            self::ARG_AFTER => [
                'name' => self::ARG_AFTER,
                'description' => 'Records after cursor',
                'type' => self::string(),
            ],
            self::ARG_LAST => [
                'name' => self::ARG_LAST,
                'description' => 'Last N records',
                'type' => self::int(),
            ],
            self::ARG_BEFORE => [
                'name' => self::ARG_BEFORE,
                'description' => 'Records before cursor',
                'type' => self::string(),
            ],
        ]);
    }
}
