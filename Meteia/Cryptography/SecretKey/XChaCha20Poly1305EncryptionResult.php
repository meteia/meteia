<?php

declare(strict_types=1);

namespace Meteia\Cryptography\SecretKey;

use Meteia\Cryptography\SecretKey;

class XChaCha20Poly1305EncryptionResult
{
    public function __construct(
        private string $ciphertext,
        private SecretKey $secret,
    ) {
    }

    public function ciphertext(): string
    {
        return $this->ciphertext;
    }

    public function secret(): SecretKey
    {
        return $this->secret;
    }
}
