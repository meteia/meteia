<?php

declare(strict_types=1);

namespace Meteia\MessageStreams;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Meteia\MessageStreams\CustomSerializers\CarbonSerializer;
use Meteia\MessageStreams\CustomSerializers\DateTimeSerializer;
use Meteia\MessageStreams\CustomSerializers\DateTimeZoneSerializer;
use Zumba\JsonSerializer\JsonSerializer;

class MessageSerializer
{
    private JsonSerializer $zjs;

    public function __construct()
    {
        $this->zjs = new JsonSerializer(null, [
            \DateTime::class => new DateTimeSerializer(),
            \DateTimeImmutable::class => new DateTimeSerializer(true),
            Carbon::class => new CarbonSerializer(),
            CarbonImmutable::class => new CarbonSerializer(true),
            \DateTimeZone::class => new DateTimeZoneSerializer(),
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
