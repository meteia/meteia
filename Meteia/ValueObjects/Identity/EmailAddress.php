<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Meteia\ValueObjects\ValueObject;

class EmailAddress extends ValueObject
{
    protected string $address;

    protected string $displayName;

    public function __construct(string $address, string $displayName)
    {
        $this->address = $address;
        $this->displayName = $displayName;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [$this->address => $this->displayName];
    }
}
