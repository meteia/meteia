<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Identity;

use Meteia\Domain\Exceptions\InvalidValueObjectException;
use Meteia\Domain\ValueObjects\ImmutablePrimitiveValueObject;

class UUID extends ImmutablePrimitiveValueObject
{
    public function __construct($value = null)
    {
        if ($value === null) {
            $value = \Ramsey\Uuid\Uuid::uuid4()->toString();
        } else {
            $value = (string) $value;

            switch (\strlen($value)) {
                case 16:
                    $value = bin2hex($value);

                    break;

                case 32:
                    $new = substr($value, 0, 8);
                    $new .= '-' . substr($value, 8, 4);
                    $new .= '-' . substr($value, 12, 4);
                    $new .= '-' . substr($value, 16, 4);
                    $new .= '-' . substr($value, 20, 12);
                    $value = $new;

                    break;

                case 36:
                    // UUID-formatted, no-op
                    break;

                default:
                    throw new InvalidValueObjectException($value, ['UUID']);
            }
        }

        $this->value = $value;
    }

    #[\Override]
    public function __toString()
    {
        return $this->value;
    }

    public function equalTo(self $other)
    {
        return hash_equals(strtolower($this), strtolower($other));
    }
}
