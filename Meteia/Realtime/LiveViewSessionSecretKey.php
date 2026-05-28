<?php

declare(strict_types=1);

namespace Meteia\Realtime;

use Meteia\Cryptography\SecretKey;
use Override;

readonly class LiveViewSessionSecretKey extends SecretKey
{
    #[Override]
    public static function prefix(): string
    {
        return 'lvs';
    }
}
