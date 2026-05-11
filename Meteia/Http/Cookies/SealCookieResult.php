<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Meteia\Cryptography\SecretKey;
use SensitiveParameter;

readonly class SealCookieResult
{
    public function __construct(
        public SealedCookie $sealedCookie,
        #[SensitiveParameter]
        public SecretKey $secret,
    ) {}
}
