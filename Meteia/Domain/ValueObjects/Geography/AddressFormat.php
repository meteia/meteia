<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Geography;

use Meteia\Domain\ValueObjects\Enum;

/**
 * Class AddressFormat.
 *
 * List of basic elements:
 *  - street (@see Street)
 *  - buildingType
 *  - buildingIdentifier
 *  - floorType
 *  - floorIdentifier
 *  - unitType
 *  - unitIdentifier
 *  - city
 *  - county
 *  - state
 *  - zip
 *  - zip4
 *  - country ( Default: CountryCode::US )
 *
 * List of basic computed elements (overrideable)
 *  - zip4separator     ( Default: nothing if there is no zip4, otherwise '-')
 *  - stateSeparator    ( Default: nothing if state != 2 char, otherwise ',' )
 *  - lineBreak         ( Default: PHP_EOL )
 *
 * List of complex elements:
 *  - buildingElement
 *  - floorElement (alias for floorElementType1)
 *  - floorElementType1
 *  - floorElementType2
 *  - unitElement
 *  - occupancyElement
 *  - areaElement
 */
class AddressFormat extends Enum
{
    public const BUILDING_ELEMENT = '%buildingType% %buildingIdentifier%';
    public const FLOOR_ELEMENT_TYPE_1 = '%floorType% %floorIdentifier%';
    public const FLOOR_ELEMENT_TYPE_2 = '%floorIdentifier% %floorType%';
    public const UNIT_ELEMENT = '%unitType% %unitIdentifier%';
    public const OCCUPANCY_IDENTIFIER = '%buildingElement% %floorElement% %unitElement%'; // occupancyElement
    public const PLACE_STATE_ZIP = '%city%%stateSeparator% %state% %zip%%zip4separator%%zip4%'; // areaElement
    public const LINE2 = '%line2%';
    public const ONE_LINE_ADDRESS = '%street% %line2% %areaElement% %country%';
    public const MULTI_LINE_ADDRESS = '%streetLineBreak%%occupancyElementLineBreak%%areaElementLineBreak%%country%';
}
