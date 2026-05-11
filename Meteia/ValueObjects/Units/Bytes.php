<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Units;

use Meteia\ValueObjects\Enum;

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

    public const MB = 1_048_576;
    public const MEGABYTE = 1_048_576;
    public const MEGABYTES = 1_048_576;

    public const GB = 1_073_741_824;
    public const GIGABYTE = 1_073_741_824;
    public const GIGABYTES = 1_073_741_824;

    public const TB = 1_099_511_627_776;
    public const TERABYTE = 1_099_511_627_776;
    public const TERABYTES = 1_099_511_627_776;

    public const PB = 1_125_899_906_842_624;
    public const PETABYTE = 1_125_899_906_842_624;
    public const PETABYTES = 1_125_899_906_842_624;

    public const EB = 1_152_921_504_606_846_976;
    public const EXABYTE = 1_152_921_504_606_846_976;
    public const EXABYTES = 1_152_921_504_606_846_976;
}
