<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Identity;

use Meteia\Domain\Contracts\Identity\EmailAddress;
use Meteia\Domain\Contracts\Identity\EmailAddresses;
use Meteia\Domain\ValueObjects\ImmutableArrayValueObject;

class ImmutableEmailAddresses extends ImmutableArrayValueObject implements EmailAddresses
{
    public const TYPE = EmailAddress::class;

    public function toArray()
    {
        $result = [];

        /** @var EmailAddress $email */
        foreach ($this->values as $email) {
            $result = array_merge($result, $email->toArray());
        }

        return $result;
    }
}
