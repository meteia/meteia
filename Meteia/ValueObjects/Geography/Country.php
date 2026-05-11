<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Geography;

use Meteia\ValueObjects\Primitive\ComplexStringLiteral as StringLiteral;

class Country
{
    public function __construct(
        protected string $code,
    ) {}

    public function __toString(): string
    {
        return $this->getName()->string();
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): StringLiteral
    {
        return CountryCodeName::getName($this->getCode());
    }
}
