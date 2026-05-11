<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Meteia\Domain\Contracts\Identity\EmailAddress;
use Meteia\Domain\Contracts\Identity\EmailAddresses;
use Meteia\ValueObjects\ImmutableArrayValueObject;
use Override;

readonly class ImmutableEmailAddresses extends ImmutableArrayValueObject implements EmailAddresses
{
    public const TYPE = EmailAddress::class;

    #[Override]
    public function toArray(): array
    {
        $result = [];

        foreach ($this->values as $email) {
            \assert($email instanceof EmailAddress);
            $result = array_merge($result, [$email->getAddress() => $email->getDisplayName()]);
        }

        return $result;
    }
}
