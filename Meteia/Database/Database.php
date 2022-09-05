<?php

declare(strict_types=1);

namespace Meteia\Database;

use Aura\Sql\ExtendedPdo;
use Meteia\ValueObjects\Identity\UniqueId;

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
        if ($value instanceof \StringBackedEnum) {
            return $value->value;
        }
        if ($value instanceof \IntBackedEnum) {
            return $value->value;
        }
        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        return $value;
    }
}
