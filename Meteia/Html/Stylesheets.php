<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Application\ApplicationResourcesBaseUri;
use Meteia\Html\Elements\Link;

class Stylesheets implements \Stringable
{
    private array $stylesheets = [];

    public function __construct(
        private ApplicationResourcesBaseUri $applicationResourcesBaseUri,
    ) {
    }

    public function __toString()
    {
        return implode('', $this->stylesheets);
    }

    public function load($href, string $integrity = null, string $crossorigin = null): void
    {
        if (str_starts_with($href, '/')) {
            $href = (string) $this->applicationResourcesBaseUri->withPath($href);
        }
        $this->stylesheets[$href] = new Link('stylesheet', $href, $integrity, $crossorigin);
    }
}
