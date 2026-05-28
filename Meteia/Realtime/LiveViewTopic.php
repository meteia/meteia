<?php

declare(strict_types=1);

namespace Meteia\Realtime;

use Meteia\Domain\Contracts\AggregateRoot;
use Meteia\ValueObjects\AggregateRootId;
use Meteia\ValueObjects\Identity\UniqueId;
use Meteia\ValueObjects\Primitive\StringLiteral;

final class LiveViewTopic extends StringLiteral
{
    public static function forAggregate(AggregateRootId $id): self
    {
        return new self(\sprintf(
            '%s.%s.%s',
            self::contextOf($id::class),
            self::aggregateOfId($id::class),
            $id->token(),
        ));
    }

    /**
     * @param class-string<AggregateRoot> $aggregateClass
     */
    public static function forNewAggregatesOf(string $aggregateClass): self
    {
        // future: forNewAggregatesOf(string $aggregateClass, Scope $scope) once parent-child
        // relationships exist (e.g. school.{schoolId} suffix).
        return new self(\sprintf(
            '%s.%s.created',
            self::contextOf($aggregateClass),
            self::aggregateOf($aggregateClass),
        ));
    }

    public static function forUser(UniqueId $userId): self
    {
        return self::forUserSubject($userId->token());
    }

    public static function forUserSubject(string $subject): self
    {
        return new self('user.' . $subject);
    }

    private static function contextOf(string $class): string
    {
        $parts = explode('\\', $class);
        $namespace = \array_slice($parts, 0, -1);

        return self::snake($namespace === [] ? '' : end($namespace));
    }

    private static function aggregateOf(string $class): string
    {
        $parts = explode('\\', $class);

        return self::snake(end($parts));
    }

    private static function aggregateOfId(string $idClass): string
    {
        $parts = explode('\\', $idClass);
        $basename = end($parts);
        if (str_ends_with($basename, 'Id')) {
            $basename = substr($basename, 0, -2);
        }

        return self::snake($basename);
    }

    private static function snake(string $value): string
    {
        $replaced = preg_replace('/(?<=[a-z0-9])([A-Z])|(?<=[A-Z])([A-Z][a-z])/', '_$1$2', $value);

        return strtolower($replaced ?? $value);
    }
}
