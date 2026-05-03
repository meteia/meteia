<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Html\Elements\Link;
use Meteia\Resources\ResourceBaseUri;

class Stylesheets implements \Stringable
{
    private array $stylesheets = [];

    public function __construct(
        private ResourceBaseUri $resourceBaseUri,
    ) {}

    #[\Override]
    public function __toString()
    {
        return implode('', $this->stylesheets);
    }

    public function load($href, ?string $integrity = null, ?string $crossorigin = null): void
    {
        if (str_starts_with($href, '/')) {
            $href = (string) $this->resourceBaseUri->withPath($href);
        }
        $this->stylesheets[$href] = new Link('stylesheet', $href, $integrity, $crossorigin);
    }
}
