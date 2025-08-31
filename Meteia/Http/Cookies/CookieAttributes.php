<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Assert\Assertion;
use Psr\Http\Message\UriInterface;

class CookieAttributes
{
    /**
     * @var \DateTime
     */
    protected $expires = '';

    /**
     * @var \DateInterval
     */
    protected $maxAge = '';

    /**
     * @var UriInterface
     */
    protected $domain = '';

    /**
     * @var UriInterface
     */
    protected $path = '/';

    /**
     * @var string
     */
    protected $secure = 'Secure';

    /**
     * @var string
     */
    protected $httpOnly = 'HttpOnly';

    /**
     * @var string
     */
    protected $sameSite = 'Lax';

    public function __toString()
    {
        $kvParts = array_filter([
            'Expires' => $this->expires ? $this->expires->format(DATE_RFC7231) : '',
            'Max-Age' => $this->maxAge,
            'Domain' => $this->domain,
            'Path' => $this->path,
            'SameSite' => $this->sameSite,
        ]);

        $kvParts = array_map(static fn($key, $value) => $key . '=' . $value, array_keys($kvParts), $kvParts);

        $flagParts = array_filter([$this->secure, $this->httpOnly]);

        $parts = array_merge($kvParts, $flagParts);

        return implode('; ', $parts);
    }

    public function withValue(string $value): self
    {
        $copy = clone $this;
        $copy->value = $value;

        return $copy;
    }

    public function withExpires(\DateTime $expires): self
    {
        // TODO: Validate

        $copy = clone $this;
        $copy->expires = $expires;

        return $copy;
    }

    public function withMaxAge(int $seconds): self
    {
        // TODO: Validate

        $copy = clone $this;
        $copy->maxAge = $seconds;

        return $copy;
    }

    public function withDomain(UriInterface $domain): self
    {
        // TODO: Validate

        $copy = clone $this;
        $copy->domain = $domain;

        return $copy;
    }

    public function withPath(UriInterface $path): self
    {
        // TODO: Validate

        $copy = clone $this;
        $copy->path = $path;

        return $copy;
    }

    public function withSecure(bool $secure): self
    {
        $copy = clone $this;
        $copy->secure = $secure ? 'Secure' : '';

        return $copy;
    }

    public function withHttpOnly(bool $httpOnly): self
    {
        $copy = clone $this;
        $copy->httpOnly = $httpOnly ? 'HttpOnly' : '';

        return $copy;
    }

    public function withSameSite(string $sameSite): self
    {
        Assertion::choice($this->sameSite, ['Strict', 'Lax']);

        $copy = clone $this;
        $copy->sameSite = $sameSite;

        return $copy;
    }
}
