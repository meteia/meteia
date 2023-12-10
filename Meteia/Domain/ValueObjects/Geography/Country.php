<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Geography;

use Meteia\Domain\ValueObjects\Primitive\ComplexStringLiteral as StringLiteral;

class Country
{
    /**
     * @var CountryCode|string
     */
    protected $code;

    /**
     * Returns a new Country object.
     *
     * @param CountryCode|string $code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Returns country name as native string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName()->string();
    }

    /**
     * Returns country code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns country name.
     *
     * @return StringLiteral
     */
    public function getName()
    {
        $code = $this->getCode();

        return CountryCodeName::getName($code);
    }
}
