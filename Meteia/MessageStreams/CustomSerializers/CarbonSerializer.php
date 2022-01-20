<?php

declare(strict_types=1);

namespace Meteia\MessageStreams\CustomSerializers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;

class CarbonSerializer
{
    public function __construct(private bool $immutable = false)
    {
    }

    public function serialize(CarbonInterface $value): array
    {
        return ['rfc3339' => $value->format(DateTimeInterface::RFC3339_EXTENDED)];
    }

    public function unserialize(array $value): CarbonInterface
    {
        if ($this->immutable) {
            return CarbonImmutable::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $value['rfc3339']);
        }

        return Carbon::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $value['rfc3339']);
    }
}
