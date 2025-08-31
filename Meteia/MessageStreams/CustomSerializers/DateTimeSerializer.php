<?php

declare(strict_types=1);

namespace Meteia\MessageStreams\CustomSerializers;

class DateTimeSerializer
{
    public function __construct(
        private bool $immutable = false,
    ) {}

    public function serialize(\DateTimeInterface $value): array
    {
        return ['rfc3339' => $value->format(\DateTimeInterface::RFC3339_EXTENDED)];
    }

    public function unserialize(array $value): \DateTimeInterface
    {
        if ($this->immutable) {
            return \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC3339_EXTENDED, $value['rfc3339']);
        }

        return \DateTime::createFromFormat(\DateTimeInterface::RFC3339_EXTENDED, $value['rfc3339']);
    }
}
