<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\Commands\CommandId;

trait DerivedCommandTypeId
{
    public static function commandTypeId(): CommandId
    {
        $class = static::class;
        $rand = hash_hmac('sha512', $class, 'BC3DE22B-735E-4493-B67A-E6A462BCE8BA', true);

        return new CommandId(substr($rand, 0, 20));
    }
}
