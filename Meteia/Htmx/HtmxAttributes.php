<?php

declare(strict_types=1);

namespace Meteia\Htmx;

use Meteia\Library\StringCase;

use function Meteia\Polyfills\array_map_assoc;

readonly class HtmxAttributes implements \Stringable
{
    public function __construct(
        private ?bool $boost = false,
        private ?string $confirm = null,
        private ?string $delete = null,
        private ?string $disable = null,
        private ?string $disinherit = null,
        private ?string $encoding = null,
        private ?string $ext = null,
        private ?string $get = null,
        private ?string $headers = null,
        private ?string $history = null,
        private ?string $historyElt = null,
        private ?string $include = null,
        private ?string $indicator = null,
        private ?string $params = null,
        private ?string $patch = null,
        private ?string $post = null,
        private ?string $preserve = null,
        private ?string $prompt = null,
        private ?string $pushUrl = null,
        private ?string $put = null,
        private ?string $replaceUrl = null,
        private ?string $request = null,
        private ?string $select = null,
        private ?string $selectOob = null,
        private ?string $swap = null,
        private ?string $swapOob = null,
        private ?string $sync = null,
        private ?string $target = null,
        private ?string $trigger = null,
        private ?string $validate = null,
        private ?string $vals = null,
    ) {
    }

    public function __toString(): string
    {
        $attrs = $this->toArray();
        $attrs = array_map(
            static function ($k, $v) {
                if (\is_bool($v) && $v) {
                    return $k;
                }

                return sprintf('%s="%s"', $k, $v);
            },
            array_keys($attrs),
            $attrs,
        );

        return implode(' ', $attrs);
    }

    public function toArray(): array
    {
        $attrs = array_filter(get_object_vars($this), static fn ($val) => !empty($val));

        return array_map_assoc(static fn ($k, $v) => ['hx-' . StringCase::kebab($k) => $v], $attrs);
    }
}
