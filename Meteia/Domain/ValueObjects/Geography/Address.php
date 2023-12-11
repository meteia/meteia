<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Geography;

use Meteia\Domain\ValueObjects\Primitive\ComplexStringLiteral as StringLiteral;

/**
 * Class Address.
 *
 * @see https://www.fgdc.gov/standards/projects/FGDC-standards-projects/street-address/05-11.2ndDraft.CompleteDoc.pdf
 */
class Address
{
    protected $replacements = [];

    /**
     * Returns a new Address object.
     *
     * @param mixed $addressInformation
     */
    public function __construct($addressInformation)
    {
        $this->buildBasicElements($addressInformation);
        $this->buildComplexElements();
    }

    /**
     * Returns a string representation of the StringLiteral in the format defined in the constructor.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getAddress();
    }

    public function getRecipient()
    {
        return $this->format('%recipient%');
    }

    public function getStreetLine()
    {
        return $this->format('%street%');
    }

    public function getLine2()
    {
        return $this->function('%line2%');
    }

    public function getOccupancyLine()
    {
        return $this->format('%line2%');
    }

    public function getCity()
    {
        return $this->format('%city%');
    }

    public function getCountry()
    {
        return $this->format('%country%');
    }

    public function getState()
    {
        return $this->format('%state%');
    }

    public function getZip()
    {
        return $this->format('%zip%');
    }

    public function getZipPlusFour()
    {
        return $this->format('%zip%%zip4separator%%zip4%');
    }

    public function getAddress()
    {
        return $this->format(AddressFormat::ONE_LINE_ADDRESS);
    }

    /**
     * Formats a string based on some inputs.
     * You can use any of the constance or make your own.
     *
     * @param string|StringLiteral $format
     *
     * @return StringLiteral
     */
    public function format($format)
    {
        $format = new StringLiteral($format);

        $parts = $format->replace(array_keys($this->replacements), array_values($this->replacements))->split(' ');

        $join = '';
        foreach ($parts as $part) {
            $part = $part->trim();
            $part = '' . $part;
            if ($part !== ' ' && $part !== '') {
                $join .= $part . ' ';
            }
        }

        $join = new StringLiteral($join);

        return $join->trim();
    }

    /**
     * Function to build the more complex elements. Order can matter!
     */
    private function buildComplexElements(): void
    {
        $this->buildLineBreakElement('street');
        $this->replacements['%line2%'] = $this->format(AddressFormat::LINE2);
        $this->replacements['%streetLineBreak%'] = $this->buildLineBreakElement('street');
        $this->replacements['%buildingElement%'] = $this->format(AddressFormat::BUILDING_ELEMENT);
        $this->replacements['%floorElement%'] = $this->format(AddressFormat::FLOOR_ELEMENT_TYPE_1);
        $this->replacements['%floorElementType1%'] = $this->format(AddressFormat::FLOOR_ELEMENT_TYPE_1);
        $this->replacements['%floorElementType2%'] = $this->format(AddressFormat::FLOOR_ELEMENT_TYPE_2);
        $this->replacements['%unitElement%'] = $this->format(AddressFormat::UNIT_ELEMENT);
        $this->replacements['%occupancyElement%'] = $this->format(AddressFormat::OCCUPANCY_IDENTIFIER);
        $this->replacements['%occupancyElementLineBreak%'] = $this->buildLineBreakElement('occupancyElement');
        $this->replacements['%areaElement%'] = $this->format(AddressFormat::PLACE_STATE_ZIP);
        $this->replacements['%areaElementLineBreak%'] = $this->buildLineBreakElement('areaElement');
    }

    private function buildLineBreakElement($baseElement)
    {
        return $this->replacements['%' . $baseElement . '%']->trim()->isEmpty()
            ? new StringLiteral('')
            : $this->replacements['%' . $baseElement . '%'] . $this->replacements['%lineBreak%'];
    }

    /**
     * Ensures that the minimal data is collected and that all values exsist.
     *
     * @param mixed $data
     *
     * @return array
     */
    private function buildBasicElements($data)
    {
        $baseArray = [
            'recipient' => '',
            'buildingType' => '',
            'buildingIdentifier' => '',
            'floorType' => '',
            'floorIdentifier' => '',
            'unitType' => '',
            'unitIdentifier' => '',
            'city' => '',
            'county' => '',
            'state' => '',
            'zip' => '',
            'zip4' => '',
            'country' => '',
            'street' => '',
            'line2' => '',
            'lineBreak' => PHP_EOL,
        ];

        foreach (array_replace($baseArray, $data) as $key => $value) {
            if (\array_key_exists($key, $baseArray)) {
                $value = new StringLiteral($value);
                $this->replacements['%' . $key . '%'] = $value->trim();
                unset($value);
            }
        }

        // Zip Separator
        $this->buildZipSeparator($data);
        // State Separator
        $this->buildStateSeparator($data);
    }

    /**
     * since the zip separator is conditional on some other data, build it later.
     *
     * @param mixed $data
     */
    private function buildZipSeparator($data): void
    {
        if (isset($data['zip4separator'])) {
            $this->replacements['%zip4separator%'] = $data['zip4separator'];
        } elseif (isset($data['zip4']) && $data['zip4'] !== '') {
            $this->replacements['%zip4separator%'] = new StringLiteral('-');
        } else {
            $this->replacements['%zip4separator%'] = new StringLiteral('');
        }
    }

    /**
     * since the state separator is conditional on some other data, build it later.
     *
     * @param mixed $data
     */
    private function buildStateSeparator($data): void
    {
        if (isset($data['stateSeparator'])) {
            $this->replacements['%stateSeparator%'] = $data['stateSeparator'];
        } else {
            $this->replacements['%stateSeparator%'] = '';
            $state = isset($data['state']) ? new StringLiteral($data['state']) : new StringLiteral('');
            if ($state->trim()->length() === 2) {
                $this->replacements['%stateSeparator%'] = ',';
            }
        }
    }
}
