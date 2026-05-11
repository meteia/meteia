<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Component;
use Meteia\Html\Metadata;
use Meteia\Html\Node;
use Meteia\Html\Scripts;
use Meteia\Html\Stylesheets;
use Override;

final class Head implements Component
{
    public function __construct(
        public readonly Title $title,
        public readonly Metadata $metadata,
        public readonly Stylesheets $stylesheets,
        public readonly Scripts $scripts,
    ) {}

    /**
     * @param iterable<Script> $scripts
     */
    public function addScripts(iterable $scripts): self
    {
        foreach ($scripts as $script) {
            $this->scripts->add($script);
        }

        return $this;
    }

    /**
     * @param iterable<Link> $links
     */
    public function addStylesheets(iterable $links): self
    {
        foreach ($links as $link) {
            $this->stylesheets->add($link);
        }

        return $this;
    }

    #[Override]
    public function render(): Node
    {
        return el('head', [], $this->title, $this->metadata, $this->scripts, $this->stylesheets);
    }
}
