<?php

declare(strict_types=1);

namespace Meteia\MessageStreams;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Meteia\MessageStreams\CustomSerializers\DateTimeSerializer;
use Meteia\MessageStreams\CustomSerializers\DateTimeZoneSerializer;
use Zumba\JsonSerializer\JsonSerializer;

class MessageSerializer
{
    private JsonSerializer $zjs;

    public function __construct()
    {
        $this->zjs = new JsonSerializer(null, [
            DateTime::class => new DateTimeSerializer(),
            DateTimeImmutable::class => new DateTimeSerializer(true),
            DateTimeZone::class => new DateTimeZoneSerializer(),
        ]);
    }

    public function serialize(mixed $value): string
    {
        return $this->zjs->serialize($value);
    }

    public function unserialize(string $value): mixed
    {
        return $this->zjs->unserialize($value);
    }
}
