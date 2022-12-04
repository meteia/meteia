<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Geography;

use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;

/**
 * @mixin Address
 */
class AddressSpec extends ObjectBehavior
{
    public function let(): void
    {
        $street = new Street('1101 N. Wilmot Rd Ste 233');
        $baseArray = [
            'recipient' => 'John',
            'street' => $street,
            'buildingType' => '',
            'buildingIdentifier' => '',
            'floorType' => '',
            'floorIdentifier' => '',
            'unitType' => '',
            'unitIdentifier' => '',
            'city' => 'Tucson',
            'county' => 'Pima',
            'country' => 'United States',
            'state' => 'AZ',
            'zip' => '85712',
            'zip4' => '5159',
        ];
        $this->beConstructedWith($baseArray);
    }

    public function it_can_print_an_address_out(): void
    {
        $this->getAddress()->shouldReturnString('1101 N. Wilmot Rd Ste 233 Tucson, AZ 85712-5159 United States');
        $this->__toString()->shouldReturnString('1101 N. Wilmot Rd Ste 233 Tucson, AZ 85712-5159 United States');
    }

    public function it_can_get_the_recipient(): void
    {
        $this->getRecipient()->shouldReturnString('John');
    }

    public function it_can_get_the_street(): void
    {
        $this->getStreetLine()->shouldReturnString('1101 N. Wilmot Rd Ste 233');
    }

    public function it_can_get_the_street_second_line(): void
    {
        $this->getOccupancyLine()->shouldReturnString('');
    }

    public function it_can_get_the_city(): void
    {
        $this->getCity()->shouldReturnString('Tucson');
    }

    public function it_can_get_the_country(): void
    {
        $this->getCountry()->shouldReturnString('United States');
    }

    public function it_can_get_the_state(): void
    {
        $this->getState()->shouldReturnString('AZ');
    }

    public function it_can_get_the_zip(): void
    {
        $this->getZip()->shouldReturnString('85712');
    }

    public function it_can_get_the_zip_plus_four(): void
    {
        $this->getZipPlusFour()->shouldReturnString('85712-5159');
    }

    public function it_can_build_blank_address(): void
    {
        $this->beConstructedWith([]);
        $this->getAddress()->shouldReturnString('');
    }

    public function it_cat_get_custom_formats(): void
    {
        $this->format('%street%')->shouldReturnString('1101 N. Wilmot Rd Ste 233');
        $this->format('%floorElement%')->shouldReturnString('');
        $this->format('%areaElement%')->shouldReturnString('Tucson, AZ 85712-5159');
        $this->format('%country%')->shouldReturnString('United States');
    }

    public function it_can_have_custom_separators(): void
    {
        $street = new Street('1101 N. Wilmot Rd Ste 233');
        $baseArray = [
            'street' => $street,
            'buildingType' => '',
            'buildingIdentifier' => '',
            'floorType' => '',
            'floorIdentifier' => '',
            'unitType' => '',
            'unitIdentifier' => '',
            'city' => 'Tucson',
            'county' => 'Pima',
            'country' => 'United States',
            'state' => 'AZ',
            'zip' => '85712',
            'zip4' => '5159',
            'zip4separator' => '#',
            'stateSeparator' => '*',
            'lineBreak' => ';',
            'floorType' => 'Floor',
            'floorIdentifier' => '2',
        ];
        $this->beConstructedWith($baseArray);
        $this->format(AddressFormat::MULTI_LINE_ADDRESS)->shouldReturnString('1101 N. Wilmot Rd Ste 233;Floor 2;Tucson* AZ 85712#5159;United States');
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMatchers(): array
    {
        return [
            'returnString' => function ($subject, $value) {
                $subject = '' . $subject;
                $value = '' . $value;
                if ($value === $subject) {
                    return true;
                }

                throw new FailureException(sprintf('Message with subject "%s" and key "%s".', $subject, $value));

                return false;
            },
        ];
    }
}
