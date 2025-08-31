<?php

declare(strict_types=1);

namespace Meteia\Cryptography;

use Meteia\ValueObjects\Identity\UniqueId;

readonly class SecretKey extends UniqueId
{
    protected const int LEN_ENCODED = 43;
    protected const int LEN_RANDOM = 32;
    protected const int LEN_TIMESTAMP = 0;

    #[\Override]
    public static function prefix(): string
    {
        return 'sk';
    }

    public function hmac(string $algorithm, string $data): Hash
    {
        return new Hash(hash_hmac($algorithm, $data, $this->bytes));
    }
}
