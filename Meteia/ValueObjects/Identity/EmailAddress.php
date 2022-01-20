<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Meteia\ValueObjects\ValueObject;

class EmailAddress extends ValueObject
{
    /** @var string */
    protected $address;

    /** @var string */
    protected $displayName;

    public function __construct($address, $displayName)
    {
        $this->address = $address;
        $this->displayName = $displayName;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function toArray()
    {
        return [$this->address => $this->displayName];
    }
}
