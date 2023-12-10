<?php

declare(strict_types=1);

namespace Meteia\Logging;

class Logfmt
{
    public function write($level, $message, array $context = []): string
    {
        $lineData = $context;

        $lineData['level'] = $level;
        $lineData['msg'] = $message;

        $lineData = array_map(static function ($k, $v) {
            // ASCII printable only
            $k = preg_replace('/[^\x20-\x7E]/', '', $k);

            if ($k === '') {
                // FIXME: Feels messy
                return '';
            }
            if (\is_bool($v)) {
                return $k . '=' . ($v ? 'true' : 'false');
            }
            if (\is_float($v)) {
                return $k . '=' . round($v, 4);
            }
            if (\is_string($v) && preg_match('/[ "]/', $v)) {
                return sprintf('%s="%s"', $k, $v);
            }

            return sprintf('%s=%s', $k, $v);
        }, array_keys($lineData), $lineData);
        $lineData = array_filter($lineData);
        sort($lineData);

        return implode(' ', $lineData);
    }
}
