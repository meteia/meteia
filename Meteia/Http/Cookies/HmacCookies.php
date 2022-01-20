<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Meteia\Configuration\Configuration;
use Psr\Http\Message\ServerRequestInterface;
use StephenHill\Base58;

class HmacCookies
{
    /**
     * @var ServerRequestInterface
     */
    private $serverRequest;

    public function __construct(ServerRequestInterface $serverRequest, Configuration $configuration)
    {
        $this->serverRequest = $serverRequest;
        $this->algo = $configuration->string('RW_HMAC_COOKIE_ALGORITHM', 'sha256');
        $this->base58 = new Base58();
    }

    /**
     * FIXME: I'm really not sure about this. Also, lots of duplicate code?
     */
    public function decodeWithLookup(string $name, string $default, callable $lookupViaUntrusted): string
    {
        $cookies = $this->serverRequest->getCookieParams();
        if (!isset($cookies[$name])) {
            return $default;
        }
        [$hmac, $value] = explode(':', $cookies[$name], 2);
        $secret = $lookupViaUntrusted($value);

        return $this->decode($name, $default, $secret);
    }

    public function decode(string $name, string $default, string $secret): string
    {
        $cookies = $this->serverRequest->getCookieParams();
        if (!isset($cookies[$name])) {
            return $default;
        }
        [$hmac, $value] = explode(':', $cookies[$name], 2);

        $expected = hash_hmac($this->algo, $value, $secret, true);
        $actual = $this->base58->decode($hmac);
        if (!hash_equals($expected, $actual)) {
            return $default;
        }

        return $value;
    }

    public function encode(string $name, string $value, string $secret): Cookie
    {
        $hmac = $this->base58->encode(hash_hmac($this->algo, $value, $secret, true));
        $value = implode(':', [$hmac, $value]);

        return new Cookie($name, $value);
    }
}
