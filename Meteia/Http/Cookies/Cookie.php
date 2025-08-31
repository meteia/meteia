<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Psr\Http\Message\ResponseInterface;

class Cookie
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $value = '';

    /**
     * @var CookieAttributes
     */
    protected $cookieAttributes;

    public function __construct(string $name, string $value, ?CookieAttributes $cookieAttributes = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->cookieAttributes = $cookieAttributes ?? new CookieAttributes();
    }

    public function hash($algo = 'sha256', $raw = false)
    {
        return hash($algo, $this->value, $raw);
    }

    public function includeIn(ResponseInterface $response): ResponseInterface
    {
        $parts = array_filter([
            sprintf('%s=%s', $this->name, $this->value),
            (string) $this->cookieAttributes,
        ]);

        return $response->withAddedHeader('Set-Cookie', implode('; ', $parts));
    }

    public function withCookieAttributes(CookieAttributes $cookieAttributes): self
    {
        $copy = clone $this;
        $copy->cookieAttributes = $cookieAttributes;

        return $copy;
    }

    public function value(): string
    {
        return $this->value;
    }
}
