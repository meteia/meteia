<?php

declare(strict_types=1);

namespace Meteia\MessageStreams\CustomSerializers;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;

class DateTimeSerializer
{
    public function __construct(
        private bool $immutable = false,
    ) {}

    /**
     * @return array{rfc3339: string}
     */
    public function serialize(DateTimeInterface $value): array
    {
        return ['rfc3339' => $value->format(DateTimeInterface::RFC3339_EXTENDED)];
    }

    /**
     * @param array{rfc3339?: string} $value
     */
    public function unserialize(array $value): DateTimeInterface
    {
        $rfc = $value['rfc3339'] ?? '';
        if ($this->immutable) {
            $dt = DateTimeImmutable::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $rfc);
        } else {
            $dt = DateTime::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $rfc);
        }
        if ($dt === false) {
            throw new RuntimeException('Unable to parse date: ' . $rfc);
        }

        return $dt;
    }
}
