<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Identity;

use Meteia\Domain\Contracts\Comparable;
use Meteia\Domain\Contracts\Identity\URI;
use Meteia\Domain\ValueObjects\Primitive\StringLiteral;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ImmutableURI extends StringLiteral implements URI
{
    public function __construct(...$segments)
    {
        $segments = array_filter($segments);
        $value = implode('/', $segments);
        parent::__construct($value);
    }

    public function symfonyResponse(): Response
    {
        return new RedirectResponse((string) $this, 303);
    }

    #[\Override]
    public function compareTo(Comparable $other)
    {
        return strcasecmp($this->toNative(), $other->toNative());
    }

    #[\Override]
    public function toNative()
    {
        return $this->value;
    }

    #[\Override]
    public function getAuthority()
    {
        return null;
    }

    #[\Override]
    public function getFragment()
    {
        return $this->getZendUri()->getFragment();
    }

    #[\Override]
    public function getHost()
    {
        return $this->getZendUri()->getHost();
    }

    #[\Override]
    public function getPath()
    {
        return $this->getZendUri()->getPath();
    }

    #[\Override]
    public function getPort()
    {
        return $this->getZendUri()->getPort();
    }

    #[\Override]
    public function getQuery()
    {
        return $this->getZendUri()->getQueryAsArray();
    }

    #[\Override]
    public function getScheme()
    {
        return $this->getZendUri()->getScheme();
    }

    #[\Override]
    public function getUserInfo()
    {
        return $this->getZendUri()->getUserInfo();
    }

    #[\Override]
    public function withFragment($fragment)
    {
        return new static($this->getZendUri()->setFragment($fragment));
    }

    #[\Override]
    public function withHost($host)
    {
        return new static($this->getZendUri()->setHost($host));
    }

    #[\Override]
    public function withPath($path)
    {
        $parts = explode('/', (string) $path);
        $parts = array_filter($parts, static fn($i) => $i !== '');
        $path = '/' . implode('/', $parts);

        return new static($this->getZendUri()->setPath($path));
    }

    #[\Override]
    public function withPort($port)
    {
        return new static($this->getZendUri()->setPort($port));
    }

    /**
     * @param array|string $query
     *
     * @return static
     */
    #[\Override]
    public function withQuery($query)
    {
        if (\is_array($query)) {
            $query = array_map('strval', $query);
        }

        return new static($this->getZendUri()->setQuery($query));
    }

    #[\Override]
    public function withScheme($scheme)
    {
        return new static($this->getZendUri()->setScheme($scheme));
    }

    #[\Override]
    public function withUserInfo($user, $password = null)
    {
        return new static($this->getZendUri()->setUserInfo(implode(':', [
            $user,
            $password,
        ])));
    }

    private function getZendUri(): \Laminas\Uri\Uri
    {
        return new \Laminas\Uri\Uri($this->value);
    }
}
