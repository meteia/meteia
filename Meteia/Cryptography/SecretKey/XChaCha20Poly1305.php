<?php

declare(strict_types=1);

namespace Meteia\Cryptography\SecretKey;

use Meteia\Cryptography\Errors\DecryptionFailed;
use Meteia\Cryptography\SecretKey;
use StephenHill\Base58;

class XChaCha20Poly1305
{
    public function __construct(private Base58 $base58)
    {
    }

    public function decrypt(string $ciphertext, string $associatedData, SecretKey $secret): XChaCha20Poly1305DecryptionResult
    {
        $ciphertext = $this->base58->decode($ciphertext);

        $nonce = mb_substr($ciphertext, 0, SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES, '8bit');
        $ciphertext = mb_substr($ciphertext, SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES, null, '8bit');

        $plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $ciphertext,
            $nonce . $associatedData,
            $nonce,
            $secret->randomBytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES),
        );
        if ($plaintext === false) {
            throw new DecryptionFailed('Either 1) ciphertext or associated data has been modified or 2) the secret is incorrect');
        }

        return new XChaCha20Poly1305DecryptionResult($plaintext);
    }

    public function encrypt(string $plaintext, string $associatedData, ?SecretKey $secret = null): XChaCha20Poly1305EncryptionResult
    {
        if (!$secret) {
            $secret = SecretKey::random();
        }
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $plaintext,
            $nonce . $associatedData,
            $nonce,
            $secret->randomBytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES),
        );
        $ciphertext = $nonce . $ciphertext;

        $ciphertext = $this->base58->encode($ciphertext);

        return new XChaCha20Poly1305EncryptionResult($ciphertext, $secret);
    }
}
