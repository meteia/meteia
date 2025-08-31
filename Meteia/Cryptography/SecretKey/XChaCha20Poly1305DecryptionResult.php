<?php

declare(strict_types=1);

namespace Meteia\Cryptography\SecretKey;

class XChaCha20Poly1305DecryptionResult
{
    public function __construct(
        public readonly string $plaintext,
    ) {}
}
