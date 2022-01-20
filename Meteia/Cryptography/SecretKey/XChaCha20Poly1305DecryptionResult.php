<?php

declare(strict_types=1);

namespace Meteia\Cryptography\SecretKey;

class XChaCha20Poly1305DecryptionResult
{
    public function __construct(private string $plaintext)
    {
    }

    public function plaintext(): string
    {
        return $this->plaintext;
    }
}
