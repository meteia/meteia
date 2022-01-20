<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Meteia\Cryptography\SecretKey\XChaCha20Poly1305;
use StephenHill\Base58;

class AeadCookies
{
    private const VERSION_1 = 'C1';

    /**
     * @var XChaCha20Poly1305
     */
    private $XChaCha20Poly1305;

    public function __construct(XChaCha20Poly1305 $XChaCha20Poly1305)
    {
        $this->base58 = new Base58();
        $this->XChaCha20Poly1305 = $XChaCha20Poly1305;
    }

    public function encode(string $name, string $value, string $secret): Cookie
    {
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $ad = implode('_', [
            self::VERSION_1,
            $name,
            $this->base58->encode($nonce),
        ]);
        $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $value,
            $ad,
            $nonce,
            $secret,
        );

        $cookieValue = implode('_', [
            self::VERSION_1,
            $this->base58->encode($nonce),
            $this->base58->encode($ciphertext),
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
            $this->base58->decode($ciphertext),
            $expectedAd,
            $this->base58->decode($nonce),
            $secret,
        );

        return $decrypted ?? $default;
    }
}
