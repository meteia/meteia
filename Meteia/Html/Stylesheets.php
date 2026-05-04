<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Html\Elements\Link;
use Meteia\Resources\ResourceBaseUri;

final class Stylesheets implements Component
{
    /** @var array<string, Link> */
    private array $stylesheets = [];

    public function __construct(
        private readonly ResourceBaseUri $resourceBaseUri,
    ) {}

    #[\Override]
    public function render(): Node
    {
        return Children::of(...array_values($this->stylesheets));
    }

    public function add(Link $link): void
    {
        $href = $this->normalize((string) $link->href);
        if ($href === (string) $link->href) {
            $this->stylesheets[$href] = $link;

            return;
        }
        $this->stylesheets[$href] = new Link(
            $link->rel,
            $href,
            $link->integrity,
            $link->crossorigin,
            $link->sizes,
            $link->type,
        );
    }

    public function load(string|\Stringable $href, ?string $integrity = null, ?string $crossorigin = null): void
    {
        $href = $this->normalize((string) $href);
        $this->stylesheets[$href] = new Link('stylesheet', $href, $integrity, $crossorigin);
    }

    private function normalize(string $href): string
    {
        if (str_starts_with($href, '/')) {
            return (string) $this->resourceBaseUri->withPath($href);
        }

        return $href;
    }
}
