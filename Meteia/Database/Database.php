<?php

declare(strict_types=1);

namespace Meteia\Database;

use Aura\Sql\ExtendedPdo;
use DateTime;
use DateTimeInterface;
use IntBackedEnum;
use Meteia\ValueObjects\Identity\UniqueId;
use Stringable;
use StringBackedEnum;

class Database extends ExtendedPdo
{
    public function prepareBindings(array $values): array
    {
        return array_map($this->prepareBoundValue(...), $values);
    }

    private function prepareBoundValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map($this->prepareBoundValue(...), $value);
        }
        if (!is_object($value)) {
            return $value;
        }
        if ($value instanceof UniqueId) {
            return $value->bytes;
        }
        if ($value instanceof StringBackedEnum) {
            return $value->value;
        }
        if ($value instanceof IntBackedEnum) {
            return $value->value;
        }
        if ($value instanceof DateTime) {
            return $value->format(MySQL::DATE);
        }
        if ($value instanceof DateTimeInterface) {
            return $value->format(MySQL::DATETIME);
        }
        if ($value instanceof Stringable) {
            return (string) $value;
        }

        return $value;
    }
}
