<?php

declare(strict_types=1);

namespace Meteia\Cryptography\SecretKey;

use Meteia\Cryptography\SecretKey;

class XChaCha20Poly1305EncryptionResult
{
    public function __construct(public readonly string $ciphertext, public readonly SecretKey $secret)
    {
    }
}
