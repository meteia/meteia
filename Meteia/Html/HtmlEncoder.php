<?php

declare(strict_types=1);

namespace Meteia\Html;

use Stringable;

final readonly class HtmlEncoder
{
    public function encode(string|Stringable|Component $node): string
    {
        return match (true) {
            $node instanceof Component => $this->encode($node->render()),
            $node instanceof Tag => $this->encodeTag($node),
            $node instanceof Children => $this->encodeChildren($node),
            default => (string) $node,
        };
    }

    private function encodeTag(Tag $tag): string
    {
        $attrs = $this->encodeAttrs($tag->attrs);
        $open = $attrs === '' ? "<{$tag->name}>" : "<{$tag->name} {$attrs}>";

        if ($tag->name->isVoid()) {
            return $attrs === '' ? "<{$tag->name} />" : "<{$tag->name} {$attrs} />";
        }

        return $open . $this->encodeChildren($tag->children) . "</{$tag->name}>";
    }

    private function encodeChildren(Children $children): string
    {
        $parts = [];
        foreach ($children as $child) {
            $parts[] = $this->encode($child);
        }

        return implode(\PHP_EOL, $parts);
    }

    private function encodeAttrs(Attrs $attrs): string
    {
        $parts = [];
        foreach ($attrs->values as $name => $value) {
            $serialized = match ($value) {
                null, false, '' => null,
                true => $name,
                default => sprintf('%s="%s"', $name === 'className' ? 'class' : $name, $this->escape((string) $value)),
            };
            if ($serialized !== null) {
                $parts[] = $serialized;
            }
        }

        return implode(' ', $parts);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, \ENT_QUOTES | \ENT_SUBSTITUTE | \ENT_HTML5);
    }
}
