<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Identity;

use Meteia\Yeso\Exceptions\InvalidUuid;
use Meteia\Yeso\ValueObjects\PrimitiveValueObject;
use Ramsey\Uuid\Uuid as RamseyUuid;

class Uuid extends PrimitiveValueObject
{
    public function __construct($value = null)
    {
        if ($value === null) {
            $value = RamseyUuid::uuid4()->toString();
        } else {
            $pattern = '/' . RamseyUuid::VALID_PATTERN . '/';

            if (!preg_match($pattern, $value)) {
                throw new InvalidUuid($value . ' is not a valid UUID');
            }
        }

        $this->value = (string) $value;
    }

    public function __toString()
    {
        return $this->value;
    }

    public function equalTo(self $other)
    {
        return hash_equals(strtolower($this->value), strtolower($this->value));
    }
}
