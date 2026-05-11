<?php

declare(strict_types=1);

namespace Meteia\Logging;

final readonly class Logfmt
{
    /**
     * @param array<array-key, mixed> $context
     */
    public function format(string $level, string $message, array $context = []): string
    {
        $lineData = $context;

        $lineData['level'] = $level;
        $lineData['msg'] = $message;

        $formatted = [];
        foreach ($lineData as $rawKey => $value) {
            $key = (string) preg_replace('/[^\x20-\x7E]/', '', (string) $rawKey);
            if ($key === '') {
                continue;
            }
            if (\is_bool($value)) {
                $formatted[] = $key . '=' . ($value ? 'true' : 'false');
                continue;
            }
            if (\is_float($value)) {
                $formatted[] = $key . '=' . round($value, 4);
                continue;
            }
            $stringValue = \is_scalar($value) || $value instanceof \Stringable ? (string) $value : '';
            if (preg_match('/[ "]/', $stringValue) === 1) {
                $formatted[] = sprintf('%s="%s"', $key, $stringValue);
                continue;
            }
            $formatted[] = sprintf('%s=%s', $key, $stringValue);
        }
        sort($formatted);

        return implode(' ', $formatted);
    }
}
