<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Meteia\Configuration\Configuration;
use Psr\Http\Message\ServerRequestInterface;
use Tuupola\Base62;

class HmacCookies
{
    private readonly string $algo;

    public function __construct(
        private readonly ServerRequestInterface $serverRequest,
        private readonly Base62 $base62,
        Configuration $configuration,
    ) {
        $this->algo = $configuration->string('RW_HMAC_COOKIE_ALGORITHM', 'sha256');
    }

    public function decode(string $name, string $default, string $secret): string
    {
        $cookies = $this->serverRequest->getCookieParams();
        if (!isset($cookies[$name])) {
            return $default;
        }
        [$hmac, $value] = explode(':', $cookies[$name], 2);

        $expected = hash_hmac($this->algo, $value, $secret, true);
        $actual = $this->base62->decode($hmac);
        if (!hash_equals($expected, $actual)) {
            return $default;
        }

        return $value;
    }

    public function encode(string $name, string $value, string $secret): Cookie
    {
        $hmac = $this->base62->encode(hash_hmac($this->algo, $value, $secret, true));
        $value = implode(':', [$hmac, $value]);

        return new Cookie($name, $value);
    }

    /**
     * FIXME: I'm really not sure about this. Also, lots of duplicate code?
     */
    private function decodeWithLookup(string $name, string $default, callable $lookupViaUntrusted): string
    {
        $cookies = $this->serverRequest->getCookieParams();
        if (!isset($cookies[$name])) {
            return $default;
        }
        [$hmac, $value] = explode(':', $cookies[$name], 2);
        $secret = $lookupViaUntrusted($value);

        return $this->decode($name, $default, $secret);
    }
}
