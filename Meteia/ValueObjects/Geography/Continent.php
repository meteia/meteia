<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Geography;

use Meteia\ValueObjects\Enum;

class Continent extends Enum
{
    public const AFRICA = 'Africa';
    public const EUROPE = 'Europe';
    public const ASIA = 'Asia';
    public const NORTH_AMERICA = 'North America';
    public const SOUTH_AMERICA = 'South America';
    public const ANTARCTICA = 'Antarctica';
    public const AUSTRALIA = 'Australia';
}
