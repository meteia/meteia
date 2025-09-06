<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Meteia\Cryptography\SecretKey;

readonly class SealCookieResult
{
    public function __construct(
        public SealedCookie $sealedCookie,
        public SecretKey $secret,
    ) {}
}
