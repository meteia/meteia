<?php

declare(strict_types=1);

namespace Meteia\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use Meteia\GraphQL\Contracts\RequestContext;
use Meteia\GraphQL\Contracts\Resolver;
use Meteia\GraphQL\ResolveWith;

class Color extends ObjectType implements Resolver
{
    use ResolveWith;

    public function __construct()
    {
        parent::__construct([
            'fields' => [
                'hex' => self::nonNull(self::string()),
                'red' => self::nonNull(self::int()),
                'green' => self::nonNull(self::int()),
                'blue' => self::nonNull(self::int()),
                'alpha' => self::nonNull(self::int()),
            ],
        ]);
    }

    public function data(mixed $root, array $args, RequestContext $requestContext): object
    {
        $color = is_string($root) ? $root : '10203040';
        [$r, $g, $b, $a] = str_split(str_pad($color, 8, 'F'), 2);

        return (object) [
            'hex' => $color,
            'red' => hexdec($r ?? '55'),
            'green' => hexdec($g ?? '55'),
            'blue' => hexdec($b ?? '55'),
            'alpha' => hexdec($a ?? 'FF'),
        ];
    }
}
