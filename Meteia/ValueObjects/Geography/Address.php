<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Geography;

use Meteia\ValueObjects\Primitive\ComplexStringLiteral as StringLiteral;

/**
 * Class Address.
 *
 * @see https://www.fgdc.gov/standards/projects/FGDC-standards-projects/street-address/05-11.2ndDraft.CompleteDoc.pdf
 */
class Address
{
    /** @var array<string, StringLiteral|string> */
    protected array $replacements = [];

    /**
     * @param array<string, mixed> $addressInformation
     */
    public function __construct(array $addressInformation)
    {
        $this->buildBasicElements($addressInformation);
        $this->buildComplexElements();
    }

    public function __toString(): string
    {
        return $this->getAddress();
    }

    public function getRecipient(): string
    {
        return $this->format('%recipient%');
    }

    public function getStreetLine(): string
    {
        return $this->format('%street%');
    }

    public function getLine2(): string
    {
        return $this->format('%line2%');
    }

    public function getOccupancyLine(): string
    {
        return $this->format('%line2%');
    }

    public function getCity(): string
    {
        return $this->format('%city%');
    }

    public function getCountry(): string
    {
        return $this->format('%country%');
    }

    public function getState(): string
    {
        return $this->format('%state%');
    }

    public function getZip(): string
    {
        return $this->format('%zip%');
    }

    public function getZipPlusFour(): string
    {
        return $this->format('%zip%%zip4separator%%zip4%');
    }

    public function getAddress(): string
    {
        return $this->format(AddressFormat::ONE_LINE_ADDRESS);
    }

    public function format(string $format): string
    {
        $formatLiteral = new StringLiteral($format);

        $replacements = array_map(static fn(StringLiteral|string $v): string => (string) $v, $this->replacements);

        $parts = $formatLiteral->replace(array_keys($replacements), array_values($replacements))->split(' ');

        $join = '';
        foreach ($parts as $part) {
            $trimmed = (string) $part->trim();
            if ($trimmed !== ' ' && $trimmed !== '') {
                $join .= $trimmed . ' ';
            }
        }

        return (string) new StringLiteral($join)->trim();
    }

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

    private function buildLineBreakElement(string $baseElement): string
    {
        $key = '%' . $baseElement . '%';
        $existing = $this->replacements[$key] ?? '';
        $existingLiteral = $existing instanceof StringLiteral ? $existing : new StringLiteral($existing);
        if ($existingLiteral->trim()->isEmpty()) {
            return '';
        }
        $lineBreak = $this->replacements['%lineBreak%'] ?? '';

        return (string) $existingLiteral . (string) $lineBreak;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildBasicElements(array $data): void
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
            if (!\array_key_exists($key, $baseArray)) {
                continue;
            }

            $literal = new StringLiteral((string) $value);
            $this->replacements['%' . $key . '%'] = $literal->trim();
        }

        $this->buildZipSeparator($data);
        $this->buildStateSeparator($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildZipSeparator(array $data): void
    {
        if (isset($data['zip4separator']) && \is_string($data['zip4separator'])) {
            $this->replacements['%zip4separator%'] = $data['zip4separator'];
        } elseif (isset($data['zip4']) && $data['zip4'] !== '') {
            $this->replacements['%zip4separator%'] = new StringLiteral('-');
        } else {
            $this->replacements['%zip4separator%'] = new StringLiteral('');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildStateSeparator(array $data): void
    {
        if (isset($data['stateSeparator']) && \is_string($data['stateSeparator'])) {
            $this->replacements['%stateSeparator%'] = $data['stateSeparator'];
        } else {
            $this->replacements['%stateSeparator%'] = '';
            $stateValue = isset($data['state']) ? (string) $data['state'] : '';
            $state = new StringLiteral($stateValue);
            if ($state->trim()->length() === 2) {
                $this->replacements['%stateSeparator%'] = ',';
            }
        }
    }
}
