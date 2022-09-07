<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Meteia\Cryptography\SecretKey;
use Meteia\Cryptography\SecretKey\XChaCha20Poly1305;
use Tuupola\Base62;

class AeadCookies
{
    private const VERSION_1 = 'C1';

    public function __construct(
        private readonly XChaCha20Poly1305 $XChaCha20Poly1305,
        private readonly Base62 $base62,
    ) {
    }

    public function encode(string $name, string $value, SecretKey $secret): Cookie
    {
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $ad = implode('_', [
            self::VERSION_1,
            $name,
            $this->base62->encode($nonce),
        ]);
        $ciphertext = $this->XChaCha20Poly1305->encrypt($value, $ad, $secret);

        $cookieValue = implode('_', [
            self::VERSION_1,
            $this->base62->encode($nonce),
            $ciphertext->ciphertext,
        ]);

        return new Cookie($name, $cookieValue);
    }

    public function decode(string $name, string $cookie, string $default, string $secret): string
    {
        $version = substr($cookie, 0, 2);
        switch ($version) {
            case self::VERSION_1:
                return $this->decodeVersion1($name, $cookie, $default, $secret);
        }

        return $default;
    }

    private function decodeVersion1(string $name, string $cookie, string $default, string $secret): string
    {
        [, $nonce, $ciphertext] = explode('_', $cookie, 3);
        $expectedAd = implode('_', [
            self::VERSION_1,
            $name,
            $nonce,
        ]);

        $decrypted = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $this->base62->decode($ciphertext),
            $expectedAd,
            $this->base62->decode($nonce),
            $secret,
        );

        return $decrypted ?? $default;
    }
}
