<?php

declare(strict_types=1);

namespace Meteia\Cryptography\SecretKey;

use Meteia\Cryptography\SecretKey;
use SensitiveParameter;

class XChaCha20Poly1305EncryptionResult
{
    public function __construct(
        public readonly string $ciphertext,
        #[SensitiveParameter]
        public readonly SecretKey $secret,
    ) {}
}
