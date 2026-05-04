<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Html\Elements\Script;
use Meteia\Resources\ResourceBaseUri;

class Scripts implements Component
{
    /** @var array<string, Script> */
    private array $scripts = [];

    public function __construct(
        private ResourceBaseUri $resourceBaseUri,
    ) {}

    #[\Override]
    public function render(): Node
    {
        return Children::of(...array_values($this->scripts));
    }

    public function load(
        string|\Stringable $src,
        $async = false,
        $defer = false,
        string $integrity = '',
        string $crossorigin = '',
    ): void {
        $src = (string) $src;
        if (str_starts_with($src, '/')) {
            $src = (string) $this->resourceBaseUri->withPath($src);
        }
        $this->scripts[$src] = new Script($src, $async, $defer, '', $integrity, $crossorigin);
    }

    public function module(string|\Stringable $src): void
    {
        $src = (string) $src;
        if (str_starts_with($src, '/')) {
            $src = (string) $this->resourceBaseUri->withPath($src);
        }
        $this->scripts[$src] = new Script($src, false, false, 'module');
    }
}
