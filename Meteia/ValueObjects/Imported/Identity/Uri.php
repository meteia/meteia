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

    public function withFragment($fragment): Uri
    {
        return new static((string) $this->getZendUri()->setFragment($fragment));
    }

    public function withHost($host): Uri
    {
        return new static((string) $this->getZendUri()->setHost($host));
    }

    public function withPath($path): Uri
    {
        if (strpos((string) $path, '/') !== 0) {
            $path = '/' . (string) $path;
        }

        return new static((string) $this->getZendUri()->setPath($path));
    }

    public function withPort($port): Uri
    {
        return new static((string) $this->getZendUri()->setPort($port));
    }

    public function withQueryArray(array $query): Uri
    {
        $query = array_map('strval', $query);

        return new static((string) $this->getZendUri()->setQuery($query));
    }

    public function withQueryString(string $query): Uri
    {
        return new static((string) $this->getZendUri()->setQuery($query));
    }

    public function withScheme($scheme): Uri
    {
        return new static((string) $this->getZendUri()->setScheme($scheme));
    }

    private function getZendUri()
    {
        return new ZendUri($this->value);
    }
}
