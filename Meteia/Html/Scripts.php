<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Application\ApplicationResourcesBaseUri;
use Meteia\Html\Elements\Script;

class Scripts implements \Stringable
{
    private array $scripts = [];

    public function __construct(
        private ApplicationResourcesBaseUri $applicationResourcesBaseUri,
    ) {}

    #[\Override]
    public function __toString(): string
    {
        return implode('', $this->scripts);
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
            $src = (string) $this->applicationResourcesBaseUri->withPath($src);
        }
        $this->scripts[$src] = new Script($src, $async, $defer, '', $integrity, $crossorigin);
    }

    public function module(string|\Stringable $src): void
    {
        $src = (string) $src;
        if (str_starts_with($src, '/')) {
            $src = (string) $this->applicationResourcesBaseUri->withPath($src);
        }
        $this->scripts[$src] = new Script($src, false, false, 'module');
    }
}
