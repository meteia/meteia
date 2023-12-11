<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Meteia\Cryptography\SecretKey;
use Meteia\Cryptography\SecretKey\XChaCha20Poly1305;

class OpenedCookie extends Cookie
{
    protected string $associatedData = '';

    public function __construct(
        string $name,
        string $value,
        CookieAttributes $cookieAttributes = null,
        string $associatedData = '',
    ) {
        parent::__construct($name, $value, $cookieAttributes);
        $this->associatedData = $associatedData;
    }

    public function seal(XChaCha20Poly1305 $XChaCha20Poly1305, SecretKey $secret = null): SealCookieResult
    {
        $ad = implode('_', array_filter([$this->name, $this->associatedData]));
        $result = $XChaCha20Poly1305->encrypt($this->value, $ad, $secret);

        $sealedCookieValue = implode('_', array_filter([$result->ciphertext, $this->associatedData]));

        $cookie = new SealedCookie($this->name, $sealedCookieValue, $this->cookieAttributes);

        return new SealCookieResult($cookie, $result->secret);
    }

    public function withAssociatedData(string $associatedData): self
    {
        $copy = clone $this;
        $copy->associatedData = $associatedData;

        return $copy;
    }

    public function associatedData(): string
    {
        return $this->associatedData;
    }
}
