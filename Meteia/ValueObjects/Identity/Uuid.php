<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Meteia\ValueObjects\Errors\ValueObjectInvalid;
use Meteia\ValueObjects\PrimitiveValueObject;
use Override;
use Stringable;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

class Uuid extends PrimitiveValueObject implements Stringable
{
    public function __construct(string|Stringable|null $value = null)
    {
        if ($value === null) {
            $value = SymfonyUuid::v4()->toRfc4122();
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
                    throw new ValueObjectInvalid($value, ['UUID']);
            }
        }

        parent::__construct($value);
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function equalTo(self $other): bool
    {
        return hash_equals(strtolower((string) $this), strtolower((string) $other));
    }
}
