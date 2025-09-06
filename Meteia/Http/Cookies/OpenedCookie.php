<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Meteia\Cryptography\SecretKey;
use Meteia\Cryptography\SecretKey\XChaCha20Poly1305;

readonly class OpenedCookie
{
    public function __construct(
        public string $name,
        public string $value,
        public string $associatedData = '',
    ) {}

    public function seal(XChaCha20Poly1305 $XChaCha20Poly1305, ?SecretKey $secret = null): SealCookieResult
    {
        $ad = implode('_', array_filter([$this->name, $this->associatedData]));
        $result = $XChaCha20Poly1305->encrypt($this->value, $ad, $secret);

        $sealedCookieValue = implode('_', array_filter([
            $result->ciphertext,
            $this->associatedData,
        ]));

        $cookie = new SealedCookie($this->name, $sealedCookieValue);

        return new SealCookieResult($cookie, $result->secret);
    }
}
