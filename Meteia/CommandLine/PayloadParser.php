<?php

declare(strict_types=1);

namespace Meteia\CommandLine;

use Meteia\Bootstrap\ApplicationNamespace;
use RuntimeException;

final readonly class PayloadParser
{
    /**
     * @param list<string> $tokens
     *
     * @return array{jsonFile: string|null, overrides: array<string, mixed>}
     */
    public function parseTokens(array $tokens): array
    {
        $jsonFile = null;
        $overrides = [];
        foreach ($tokens as $tok) {
            if (str_starts_with($tok, '@')) {
                $jsonFile = substr($tok, 1);
                continue;
            }
            if (!str_starts_with($tok, '--')) {
                continue;
            }
            $eq = strpos($tok, '=');
            if ($eq === false) {
                continue;
            }
            $k = substr($tok, 2, $eq - 2);
            $v = substr($tok, $eq + 1);
            $overrides[$k] = $v;
        }

        return ['jsonFile' => $jsonFile, 'overrides' => $overrides];
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    public function load(?string $jsonFile, array $overrides): array
    {
        $data = [];
        if ($jsonFile !== null) {
            if (!file_exists($jsonFile) || !is_readable($jsonFile)) {
                throw new RuntimeException('Cannot read JSON payload file: ' . $jsonFile);
            }
            $content = file_get_contents($jsonFile);
            if ($content === false) {
                throw new RuntimeException('Cannot read JSON payload file: ' . $jsonFile);
            }
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($decoded)) {
                throw new RuntimeException('JSON payload file must contain an object: ' . $jsonFile);
            }

            foreach ($decoded as $key => $value) {
                if (!is_string($key)) {
                    throw new RuntimeException('JSON payload file must contain an object: ' . $jsonFile);
                }

                $data[$key] = $value;
            }
        }

        foreach ($overrides as $path => $value) {
            Dot::set($data, $path, $value);
        }

        /** @var array<string, mixed> $data */
        return $data;
    }

    public function resolve(string $dotted, ApplicationNamespace $namespace, string $marker): ?string
    {
        $fqcn = str_replace(['.', '/'], '\\', $dotted);
        if (class_exists($fqcn) && is_subclass_of($fqcn, $marker)) {
            return $fqcn;
        }
        $prefix = rtrim((string) $namespace, '\\') . '\\';
        $candidate = $prefix . ltrim($fqcn, '\\');
        if (class_exists($candidate) && is_subclass_of($candidate, $marker)) {
            return $candidate;
        }

        return null;
    }
}
