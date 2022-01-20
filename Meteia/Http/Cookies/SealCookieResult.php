<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Meteia\Cryptography\SecretKey;

class SealCookieResult
{
    public function __construct(private SealedCookie $sealedCookie, private SecretKey $secret)
    {
    }

    public function sealedCookie(): SealedCookie
    {
        return $this->sealedCookie;
    }

    public function secret(): SecretKey
    {
        return $this->secret;
    }
}
