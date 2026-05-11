<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Geography;

use Meteia\ValueObjects\Primitive\ComplexStringLiteral as StringLiteral;

class Country
{
    /**
     * @var CountryCode|string
     */
    protected $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function __toString()
    {
        return $this->getName()->string();
    }

    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return StringLiteral
     */
    public function getName()
    {
        $code = $this->getCode();

        return CountryCodeName::getName($code);
    }
}
