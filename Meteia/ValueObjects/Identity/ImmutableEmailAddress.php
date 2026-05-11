<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Meteia\Domain\Contracts\Identity\EmailAddress;
use Meteia\ValueObjects\ValueObject;
use Override;

class ImmutableEmailAddress extends ValueObject implements EmailAddress
{
    public function __construct(
        protected string $address,
        protected string $displayName,
    ) {}

    #[Override]
    public function getAddress(): string
    {
        return $this->address;
    }

    #[Override]
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
