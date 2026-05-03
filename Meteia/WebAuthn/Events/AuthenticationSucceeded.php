<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Events;

use Meteia\Events\Event;
use Webauthn\CredentialRecord;

readonly class AuthenticationSucceeded implements Event
{
    public function __construct(
        public CredentialRecord $credentialRecord,
        public ?string $userHandle,
    ) {}
}
