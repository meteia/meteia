<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Attrs;
use Meteia\Html\Children;
use Meteia\Html\Component;
use Meteia\Html\Tag;
use Meteia\Html\TagName;
use NoDiscard;
use Stringable;

function html(string $raw): string
{
    return htmlspecialchars($raw, \ENT_QUOTES | \ENT_SUBSTITUTE | \ENT_HTML5);
}

/**
 * @param array<int|string, mixed> $attributes
 */
function attributes(array $attributes): string
{
    if (\count($attributes) === 0) {
        return '';
    }
    $rendered = [];
    foreach ($attributes as $name => $value) {
        $serialized = match ($value) {
            null, false, '' => null,
            true => (string) $name,
            default => \sprintf('%s="%s"', $name, html((string) $value)),
        };
        if ($serialized !== null) {
            $rendered[] = $serialized;
        }
    }

    return implode(' ', $rendered);
}

/**
 * @param array<int|string, mixed> $attributes
 */
#[NoDiscard]
function el(string $name, array $attributes = [], string|Stringable|Component ...$children): Tag
{
    return new Tag(TagName::of($name), Attrs::from($attributes), Children::of(...$children));
}
