<?php

declare(strict_types=1);

namespace Meteia\Htmx;

use Meteia\Library\StringCase;

class HtmxAttributes implements \Stringable
{
    /** @noinspection PhpPropertyOnlyWrittenInspection */
    public function __construct(
        private readonly ?bool $boost = false,
        private readonly ?string $confirm = null,
        private readonly ?string $delete = null,
        private readonly ?string $disable = null,
        private readonly ?string $disinherit = null,
        private readonly ?string $encoding = null,
        private readonly ?string $ext = null,
        private readonly ?string $get = null,
        private readonly ?string $headers = null,
        private readonly ?string $history = null,
        private readonly ?string $historyElt = null,
        private readonly ?string $include = null,
        private readonly ?string $indicator = null,
        private readonly ?string $params = null,
        private readonly ?string $patch = null,
        private readonly ?string $post = null,
        private readonly ?string $preserve = null,
        private readonly ?string $prompt = null,
        private readonly ?string $pushUrl = null,
        private readonly ?string $put = null,
        private readonly ?string $replaceUrl = null,
        private readonly ?string $request = null,
        private readonly ?string $select = null,
        private readonly ?string $selectOob = null,
        private readonly ?string $swap = null,
        private readonly ?string $swapOob = null,
        private readonly ?string $sync = null,
        private readonly ?string $target = null,
        private readonly ?string $trigger = null,
        private readonly ?string $validate = null,
        private readonly ?string $vals = null,
    ) {
    }

    public function __toString(): string
    {
        $attrs = array_filter(get_object_vars($this), static fn ($val) => !empty($val));
        $attrs = array_map(
            static function ($k, $v) {
                if (\is_bool($v) && $v) {
                    return $k;
                }

                return sprintf('hx-%s="%s"', StringCase::kebab($k), $v);
            },
            array_keys($attrs),
            $attrs,
        );

        return implode(' ', $attrs);
    }
}
