<?php

declare(strict_types=1);

namespace Meteia\Http\Csrf;

use Meteia\Cryptography\Errors\DecryptionFailed;
use Meteia\Cryptography\SecretKey\XChaCha20Poly1305;
use Meteia\Http\Errors\InvalidCsrfToken;

readonly class CsrfTokens
{
    public function __construct(
        private XChaCha20Poly1305 $XChaCha20Poly1305,
        private CsrfSecretKey $secretKey,
    ) {}

    public function sealedToken(string $name, int $secondsValid): string
    {
        $expiresAt = time() + $secondsValid;

        return $this->XChaCha20Poly1305->encrypt((string) $expiresAt, $name, $this->secretKey)->ciphertext;
    }

    public function assertValidToken(string $name, string $ciphertext): void
    {
        try {
            $expiresAt = (int) $this->XChaCha20Poly1305->decrypt($ciphertext, $name, $this->secretKey)->plaintext;
            if (time() > $expiresAt) {
                throw new InvalidCsrfToken('Token Expired');
            }
        } catch (DecryptionFailed) {
            throw new InvalidCsrfToken('Token Invalid');
        }
    }
}
