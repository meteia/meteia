<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Identity;

use Meteia\Yeso\ValueObjects\Primitives\StringLiteral;
use Zend\Uri\Uri as ZendUri;

class Uri extends StringLiteral
{
    public function __construct(string $uri)
    {
        parent::__construct((string) $uri);
    }

    public function withFragment($fragment): self
    {
        return new static((string) $this->getZendUri()->setFragment($fragment));
    }

    public function withHost($host): self
    {
        return new static((string) $this->getZendUri()->setHost($host));
    }

    public function withPath($path): self
    {
        if (!str_starts_with((string) $path, '/')) {
            $path = '/' . (string) $path;
        }

        return new static((string) $this->getZendUri()->setPath($path));
    }

    public function withPort($port): self
    {
        return new static((string) $this->getZendUri()->setPort($port));
    }

    public function withQueryArray(array $query): self
    {
        $query = array_map('strval', $query);

        return new static((string) $this->getZendUri()->setQuery($query));
    }

    public function withQueryString(string $query): self
    {
        return new static((string) $this->getZendUri()->setQuery($query));
    }

    public function withScheme($scheme): self
    {
        return new static((string) $this->getZendUri()->setScheme($scheme));
    }

    private function getZendUri()
    {
        return new ZendUri($this->value);
    }
}
