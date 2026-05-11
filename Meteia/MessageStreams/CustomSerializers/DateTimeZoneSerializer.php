<?php

declare(strict_types=1);

namespace Meteia\MessageStreams\CustomSerializers;

use DateTimeZone;

class DateTimeZoneSerializer
{
    /**
     * @return array{tz: string}
     */
    public function serialize(DateTimeZone $value): array
    {
        return ['tz' => $value->getName()];
    }

    /**
     * @param array{tz?: string} $value
     */
    public function unserialize(array $value): DateTimeZone
    {
        return new DateTimeZone($value['tz'] ?? 'UTC');
    }
}
