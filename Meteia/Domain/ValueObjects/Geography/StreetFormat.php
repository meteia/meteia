<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Geography;

use Meteia\Domain\ValueObjects\Enum;

class StreetFormat extends Enum
{
    public const ADDRESS_NUMBER = '%addressNumberPrefix% %addressNumberValue% %addressNumberSuffix%';
    public const PRE_STREET_NAME = '%streetNamePreModifier% %streetNamePreDirectional% %streetNamePreType%';
    public const POST_STREET_NAME = '%streetNamePostType% %streetNamePostDirectional% %streetNamePostModifier%';
    public const FULL_STREET_NAME = '%preStreetName% %streetNameValue% %postStreetName%';
    public const COMPLETE_STREET_NAME = '%completeAddressNumber% %completeStreetName%';
}
