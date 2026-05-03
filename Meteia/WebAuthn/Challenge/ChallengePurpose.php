<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Challenge;

enum ChallengePurpose: string
{
    case Registration = 'registration';
    case Authentication = 'authentication';

    public function cookieName(): string
    {
        return 'meteia_webauthn_' . $this->value;
    }
}
