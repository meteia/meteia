<?php

declare(strict_types=1);

namespace Meteia\Database;

use BackedEnum;
use Exception;
use Meteia\Library\StringCase;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

trait FromDatabase
{
    private static array $parameterCache = [];

    public static function fromDatabase(object $row): static
    {
        $parameters = array_map(
            fn (callable $make) => $make($row),
            static::constructorDatabaseColumnNames(),
        );

        return new static(...$parameters);
    }

    private static function constructorDatabaseColumnNames(): array
    {
        if (!isset(static::$parameterCache[static::class])) {
            $reflection = new ReflectionClass(static::class);
            $constructor = $reflection->getConstructor();
            if ($constructor === null) {
                throw new Exception('Missing Constructor');
            }

            static::$parameterCache[static::class] = array_map(
                function (ReflectionParameter $parameter) {
                    $columnName = StringCase::snake($parameter->getName());
                    $expectedType = $parameter->getType();
                    $expectedTypeClass = $expectedType->getName();
                    if (!$expectedType->isBuiltin() && $expectedType instanceof ReflectionNamedType) {
                        return function (object $row) use ($columnName, $expectedTypeClass) {
                            if (!isset($row->$columnName)) {
                                return null;
                            }
                            if (is_subclass_of($expectedTypeClass, BackedEnum::class)) {
                                return $expectedTypeClass::from($row->$columnName);
                            }

                            return new $expectedTypeClass($row->$columnName);
                        };
                    }
                    if ($expectedTypeClass === 'bool') {
                        return fn (object $row) => (bool) $row->$columnName;
                    }
                    if ($expectedTypeClass === 'array') {
                        return fn (object $row) => json_decode($row->$columnName, true);
                    }

                    return fn (object $row) => $row->$columnName;
                },
                $constructor->getParameters(),
            );
        }

        return static::$parameterCache[static::class];
    }
}
