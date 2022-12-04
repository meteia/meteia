<?php

declare(strict_types=1);

namespace Meteia\Domain\Entities;

use Meteia\Domain\Contracts\Entity;
use Meteia\Domain\Exceptions\ImmutableEntityException;

class ImmutableEntity implements Entity
{
    public function __set($key, $value): void
    {
        throw new ImmutableEntityException("Unable to set '$key' as " . get_class($this) . ' is immutable.');
    }

    public function jsonSerialize()
    {
        $data = [];
        foreach (get_object_vars($this) as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }
}
