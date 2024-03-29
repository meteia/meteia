<?php

declare(strict_types=1);

namespace Meteia\Library;

abstract class StringCase
{
    public static function camel(string $string): string
    {
        return lcfirst(self::pascal($string));
    }

    public static function capitalized(string $string, string $delimiters = " \n\t\r\0\x0B-"): string
    {
        return ucwords($string, $delimiters);
    }

    public static function pascal(string $string): string
    {
        return str_replace([' ', '_', '-'], '', ucwords($string, ' _-'));
    }

    public static function screamingSnake(string $string): string
    {
        return mb_strtoupper(self::snake($string));
    }

    public static function snake(string $string): string
    {
        $string = preg_replace('~(?<=\\w)([A-Z])~u', '_$1', $string);
        if ($string === null) {
            throw new \Exception('preg_replace failed during replacement');
        }

        return mb_strtolower($string);
    }

    /**
     * @see https://github.com/doctrine/inflector/blob/2.0.x/lib/Doctrine/Inflector/Inflector.php
     *
     * @license https://github.com/doctrine/inflector/blob/2.0.x/LICENSE
     */
    public static function kebab(string $string): string
    {
        $rules = implode(' ', [
            ':: Any-Latin;',
            ':: Latin-ASCII;',
            ':: NFD;',
            ':: [:Nonspacing Mark:] Remove;',
            ':: NFC;',
        ]);
        $transliterator = \Transliterator::createFromRules($rules, \Transliterator::FORWARD);
        $normalized = $transliterator->transliterate($string);

        $replacements = [
            '/\W/' => ' ',
            '/([A-Z]+)([A-Z][a-z])/' => '\1_\2',
            '/([a-z\d])([A-Z])/' => '\1_\2',
            '/[^A-Z^a-z^0-9^\/]+/' => '-',
        ];

        $urlized = $normalized;

        foreach ($replacements as $pattern => $replacement) {
            $replaced = preg_replace($pattern, $replacement, $urlized);

            if ($replaced === null) {
                throw new \RuntimeException(sprintf('preg_replace returned null for value "%s"', $urlized));
            }

            $urlized = $replaced;
        }
        $lowered = mb_strtolower($urlized);

        return trim($lowered, '-');
    }

    public static function screamingKebab(string $string): string
    {
        return mb_strtoupper(self::kebab($string));
    }
}
