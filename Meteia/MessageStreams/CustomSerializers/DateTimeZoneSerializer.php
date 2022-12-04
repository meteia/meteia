<?php

declare(strict_types=1);

namespace Meteia\MessageStreams\CustomSerializers;

use DateTimeZone;

class DateTimeZoneSerializer
{
    public function serialize(DateTimeZone $value): array
    {
        return ['tz' => $value->getName()];
    }

    public function unserialize(array $value): DateTimeZone
    {
        return new DateTimeZone($value['tz']);
    }
}
