<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Geography;

use Meteia\ValueObjects\Enum;

class AddressFormat extends Enum
{
    public const BUILDING_ELEMENT = '%buildingType% %buildingIdentifier%';
    public const FLOOR_ELEMENT_TYPE_1 = '%floorType% %floorIdentifier%';
    public const FLOOR_ELEMENT_TYPE_2 = '%floorIdentifier% %floorType%';
    public const UNIT_ELEMENT = '%unitType% %unitIdentifier%';
    public const OCCUPANCY_IDENTIFIER = '%buildingElement% %floorElement% %unitElement%';
    public const PLACE_STATE_ZIP = '%city%%stateSeparator% %state% %zip%%zip4separator%%zip4%';
    public const LINE2 = '%line2%';
    public const ONE_LINE_ADDRESS = '%street% %line2% %areaElement% %country%';
    public const MULTI_LINE_ADDRESS = '%streetLineBreak%%occupancyElementLineBreak%%areaElementLineBreak%%country%';
}
