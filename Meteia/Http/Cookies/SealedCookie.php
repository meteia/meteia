<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Meteia\Cryptography\SecretKey;
use Meteia\Cryptography\SecretKey\XChaCha20Poly1305;

class SealedCookie extends Cookie
{
    public function open(XChaCha20Poly1305 $XChaCha20Poly1305, SecretKey $secret): OpenedCookie
    {
        [$ciphertext, $associatedData] = explode('_', $this->value, 2);
        $ad = implode('_', array_filter([
            $this->name,
            $associatedData,
        ]));

        $result = $XChaCha20Poly1305->decrypt($ciphertext, $ad, $secret);

        $cookie = new OpenedCookie($this->name, $result->plaintext, $this->cookieAttributes);

        return $cookie->withAssociatedData($associatedData);
    }

    public function associatedData(): string
    {
        [, $associatedData] = explode('_', $this->value, 2);

        return $associatedData;
    }
}
