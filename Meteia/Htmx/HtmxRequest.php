<?php

declare(strict_types=1);

namespace Meteia\Htmx;

use Psr\Http\Message\ServerRequestInterface;

class HtmxRequest
{
    public function __construct(
        private readonly ServerRequestInterface $request,
    ) {}

    public function isBoosted(): bool
    {
        return $this->request->getHeaderLine('hx-boosted') === 'true';
    }

    public function isHtmx(): bool
    {
        return $this->request->hasHeader('hx-request');
    }
}
