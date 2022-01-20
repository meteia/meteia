<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Geography;

use Meteia\Domain\ValueObjects\Primitive\ComplexStringLiteral as StringLiteral;

/**
 * Class Street.
 *
 * @see https://www.fgdc.gov/standards/projects/FGDC-standards-projects/street-address/05-11.2ndDraft.CompleteDoc.pdf
 */
class Street
{
    protected $replacements = [];

    /**
     * Returns a new Street object.
     *
     * @param mixed  $streetNumber This can be either the street number, the street name (if street name not provided)
     *                             or an array of basic street values. @see StreetFormat for list of basic values.
     * @param string $streetName   [optional] the street name
     */
    public function __construct($streetNumber, $streetName = null)
    {
        if (!is_null($streetName)) {
            $this->buildBasicElements([
                    'addressNumberValue' => $streetNumber,
                    'streetNameValue' => $streetName,
                ]);
        } elseif (is_string($streetNumber)) {
            $this->buildBasicElements([
                'streetNameValue' => $streetNumber,
            ]);
        } else {
            $this->buildBasicElements($streetNumber);
        }
        $this->buildComplexElements();
    }

    /**
     * Complex Element: Complete Address Number.
     *
     * @return StringLiteral
     */
    public function getNumber()
    {
        return $this->format(StreetFormat::ADDRESS_NUMBER);
    }

    /**
     * Official name of a street as assigned by a local governing authority, or an alternate (alias) name that is used
     *      and recognized.
     *
     * @return StringLiteral
     */
    public function getStreetName()
    {
        return $this->format(StreetFormat::FULL_STREET_NAME);
    }

    /**
     * Gets the full street name including numbers.
     *
     * @return StringLiteral
     */
    public function getCompleteStreet()
    {
        return $this->format(StreetFormat::COMPLETE_STREET_NAME);
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
        $formats = $format->replace(array_keys($this->replacements), array_values($this->replacements))->split(' ');
        $join = '';
        foreach ($formats as $part) {
            $part = '' . $part;
            if ($part !== ' ') {
                $join .= $part . ' ';
            }
        }

        $join = new StringLiteral($join);

        return $join->trim();
    }

    /**
     * Returns a string representation of the StringLiteral in the format defined in the constructor.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getCompleteStreet()->string();
    }

    private function buildComplexElements()
    {
        $this->replacements['%completeAddressNumber%'] = $this->format(StreetFormat::ADDRESS_NUMBER);
        $this->replacements['%preStreetName%'] = $this->format(StreetFormat::PRE_STREET_NAME);
        $this->replacements['%postStreetName%'] = $this->format(StreetFormat::POST_STREET_NAME);
        $this->replacements['%completeStreetName%'] = $this->format(StreetFormat::FULL_STREET_NAME);
    }

    /**
     * Ensures that the minimal data is collected and that all values exsist.
     *
     * @param $data
     *
     * @return array
     */
    private function buildBasicElements($data)
    {
        $baseArray = [
            'addressNumberPrefix' => '',
            'addressNumberValue' => '',
            'addressNumberSuffix' => '',
            'streetNamePreModifier' => '',
            'streetNamePreDirectional' => '',
            'streetNamePreType' => '',
            'streetNameValue' => '',
            'streetNamePostType' => '',
            'streetNamePostDirectional' => '',
            'streetNamePostModifier' => '',
        ];

        foreach (array_replace($baseArray, $data) as $key => $value) {
            if (array_key_exists($key, $baseArray)) {
                $this->replacements['%' . $key . '%'] = new StringLiteral($value);
            }
        }
    }
}
