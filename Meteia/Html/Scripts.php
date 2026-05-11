<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Html\Elements\Script;
use Meteia\Resources\ResourceBaseUri;
use Override;
use Stringable;

final class Scripts implements Component
{
    /** @var array<string, Script> */
    private array $scripts = [];

    public function __construct(
        private readonly ResourceBaseUri $resourceBaseUri,
    ) {}

    #[Override]
    public function render(): Node
    {
        return Children::of(...array_values($this->scripts));
    }

    public function add(Script $script): void
    {
        $src = $this->normalize($script->src);
        if ($src === $script->src) {
            $this->scripts[$src] = $script;

            return;
        }
        $this->scripts[$src] = new Script(
            $src,
            $script->async,
            $script->defer,
            $script->type,
            $script->integrity,
            $script->crossorigin,
        );
    }

    public function load(
        string|Stringable $src,
        bool $async = false,
        bool $defer = false,
        string $integrity = '',
        string $crossorigin = '',
    ): void {
        $src = $this->normalize((string) $src);
        $this->scripts[$src] = new Script($src, $async, $defer, '', $integrity, $crossorigin);
    }

    public function module(string|Stringable $src): void
    {
        $src = $this->normalize((string) $src);
        $this->scripts[$src] = new Script($src, false, false, 'module');
    }

    private function normalize(string $src): string
    {
        if (str_starts_with($src, '/')) {
            return (string) $this->resourceBaseUri->withPath($src);
        }

        return $src;
    }
}
