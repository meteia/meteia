<?php

declare(strict_types=1);

use Meteia\Configuration\Configuration;
use Meteia\Cryptography\MasterKey;

return [
    MasterKey::class => static function (Configuration $configuration): MasterKey {
        // $encoded = base64_decode();
        // SODIUM_CRYPTO_KDF_KEYBYTES

        return new MasterKey($configuration->string('CRYPTOGRAPHY_MASTER_KEY', ''));
    },
];
