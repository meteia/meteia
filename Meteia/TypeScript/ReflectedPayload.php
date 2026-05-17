<?php

declare(strict_types=1);

namespace Meteia\TypeScript;

use ReflectionClass;
use ReflectionParameter;

final readonly class ReflectedPayload
{
    /**
     * @param class-string $className
     */
    public function __construct(
        private string $className,
        private string $suffix,
    ) {}

    public function name(): string
    {
        $name = trim(str_replace('\\', '_', $this->className), '_') . $this->suffix . 'Payload';

        return preg_replace('/[^A-Za-z0-9_]/', '_', $name) ?? $name;
    }

    public function declaration(): string
    {
        $parameters = $this->parameters();
        if ($parameters === []) {
            return sprintf("export type %s = Readonly<Record<string, never>>;\n", $this->name());
        }

        $lines = [sprintf('export type %s = {', $this->name())];
        foreach ($parameters as $parameter) {
            $optional = $parameter->isDefaultValueAvailable() ? '?' : '';
            $type = new PhpTypeScriptType($parameter->getType());
            $lines[] = sprintf('  readonly %s%s: %s;', $parameter->getName(), $optional, $type->declaration());
        }
        $lines[] = "};\n";

        return implode("\n", $lines);
    }

    /**
     * @return list<ReflectionParameter>
     */
    private function parameters(): array
    {
        $class = new ReflectionClass($this->className);
        $constructor = $class->getConstructor();
        if ($constructor === null) {
            return [];
        }

        return array_values($constructor->getParameters());
    }
}
