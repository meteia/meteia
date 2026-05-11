<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Geography;

use Meteia\ValueObjects\Primitive\ComplexStringLiteral as StringLiteral;
use Stringable;

/**
 * Class Street.
 *
 * @see https://www.fgdc.gov/standards/projects/FGDC-standards-projects/street-address/05-11.2ndDraft.CompleteDoc.pdf
 */
class Street
{
    /** @var array<string, StringLiteral> */
    protected array $replacements = [];

    /**
     * @param array<string, mixed>|string $streetNumber This can be either the street number, the street name (if
     *                                                  street name not provided) or an array of basic street values.
     */
    public function __construct(array|string $streetNumber, ?string $streetName = null)
    {
        if ($streetName !== null) {
            \assert(\is_string($streetNumber));
            $this->buildBasicElements([
                'addressNumberValue' => $streetNumber,
                'streetNameValue' => $streetName,
            ]);
        } elseif (\is_string($streetNumber)) {
            $this->buildBasicElements([
                'streetNameValue' => $streetNumber,
            ]);
        } else {
            $this->buildBasicElements($streetNumber);
        }
        $this->buildComplexElements();
    }

    public function __toString(): string
    {
        return $this->getCompleteStreet()->string();
    }

    public function getNumber(): StringLiteral
    {
        return $this->format(StreetFormat::ADDRESS_NUMBER);
    }

    public function getStreetName(): StringLiteral
    {
        return $this->format(StreetFormat::FULL_STREET_NAME);
    }

    public function getCompleteStreet(): StringLiteral
    {
        return $this->format(StreetFormat::COMPLETE_STREET_NAME);
    }

    public function format(string|Stringable $format): StringLiteral
    {
        $literal = new StringLiteral((string) $format);
        $replacements = array_map(static fn(StringLiteral $v): string => $v->string(), $this->replacements);
        $formats = $literal->replace(array_keys($replacements), array_values($replacements))->split(' ');
        $join = '';
        foreach ($formats as $part) {
            $partStr = $part->string();
            if ($partStr !== ' ') {
                $join .= $partStr . ' ';
            }
        }

        return new StringLiteral($join)->trim();
    }

    private function buildComplexElements(): void
    {
        $this->replacements['%completeAddressNumber%'] = $this->format(StreetFormat::ADDRESS_NUMBER);
        $this->replacements['%preStreetName%'] = $this->format(StreetFormat::PRE_STREET_NAME);
        $this->replacements['%postStreetName%'] = $this->format(StreetFormat::POST_STREET_NAME);
        $this->replacements['%completeStreetName%'] = $this->format(StreetFormat::FULL_STREET_NAME);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildBasicElements(array $data): void
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
            if (!\array_key_exists($key, $baseArray)) {
                continue;
            }

            $this->replacements['%' . $key . '%'] = new StringLiteral((string) $value);
        }
    }
}
