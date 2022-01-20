<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Units;

use Meteia\Domain\ValueObjects\Enum;

/**
 * Contains enumerable byte-size values.
 */
class Bytes extends Enum
{
    public const B = 1;
    public const BYTE = 1;
    public const BYTES = 1;

    public const KB = 1024;
    public const KILOBYTE = 1024;
    public const KILOBYTES = 1024;

    public const MB = 1048576;
    public const MEGABYTE = 1048576;
    public const MEGABYTES = 1048576;

    public const GB = 1073741824;
    public const GIGABYTE = 1073741824;
    public const GIGABYTES = 1073741824;

    public const TB = 1099511627776;
    public const TERABYTE = 1099511627776;
    public const TERABYTES = 1099511627776;

    public const PB = 1125899906842624;
    public const PETABYTE = 1125899906842624;
    public const PETABYTES = 1125899906842624;

    public const EB = 1152921504606846976;
    public const EXABYTE = 1152921504606846976;
    public const EXABYTES = 1152921504606846976;
}
