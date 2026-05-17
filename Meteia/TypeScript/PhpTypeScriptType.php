<?php

declare(strict_types=1);

namespace Meteia\TypeScript;

use BackedEnum;
use DateTimeInterface;
use Meteia\Domain\Contracts\Identity\Uri;
use Meteia\ValueObjects\Contracts\Identifier;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Stringable;

final readonly class PhpTypeScriptType
{
    public function __construct(
        private ?ReflectionType $type,
    ) {}

    public function declaration(): string
    {
        if ($this->type === null) {
            return 'unknown';
        }

        return match (true) {
            $this->type instanceof ReflectionNamedType => $this->named($this->type),
            $this->type instanceof ReflectionUnionType => $this->union($this->type),
            $this->type instanceof ReflectionIntersectionType => 'unknown',
            default => 'unknown',
        };
    }

    private function union(ReflectionUnionType $type): string
    {
        $types = [];
        foreach ($type->getTypes() as $part) {
            if (!$part instanceof ReflectionNamedType) {
                return 'unknown';
            }
            $types[] = $this->named($part);
        }
        $types = array_values(array_unique($types));
        sort($types);

        return implode(' | ', $types);
    }

    private function named(ReflectionNamedType $type): string
    {
        $name = $type->getName();
        $declaration = match ($name) {
            'string' => 'string',
            'int', 'float' => 'number',
            'bool' => 'boolean',
            'array' => 'readonly unknown[] | { readonly [key: string]: unknown }',
            'null' => 'null',
            default => $this->object($name),
        };

        if (!$type->allowsNull() || $declaration === 'null' || str_contains($declaration, 'null')) {
            return $declaration;
        }

        return $declaration . ' | null';
    }

    private function object(string $name): string
    {
        if (!class_exists($name) && !interface_exists($name) && !enum_exists($name)) {
            return 'unknown';
        }

        if (enum_exists($name) && is_subclass_of($name, BackedEnum::class)) {
            return $this->backedEnum($name);
        }

        if (is_a($name, DateTimeInterface::class, true)) {
            return 'string';
        }

        if (
            $this->isA($name, Identifier::class)
            || $this->isA($name, Uri::class)
            || $this->isA($name, Stringable::class)
        ) {
            return 'string';
        }

        return 'unknown';
    }

    private function isA(string $name, string $type): bool
    {
        return $name === $type || is_subclass_of($name, $type);
    }

    /**
     * @param class-string $name
     */
    private function backedEnum(string $name): string
    {
        $cases = [];
        $enum = new ReflectionEnum($name);
        foreach ($enum->getCases() as $case) {
            \assert($case instanceof ReflectionEnumBackedCase, 'backed enum cases expose backing values');
            $backingValue = $case->getBackingValue();
            $cases[] = is_int($backingValue) ? (string) $backingValue : $this->quoted($backingValue);
        }
        sort($cases);

        return implode(' | ', $cases);
    }

    private function quoted(string $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }
}
