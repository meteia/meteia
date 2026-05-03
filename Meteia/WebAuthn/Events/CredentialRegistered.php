<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Events;

use Meteia\Events\Event;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredentialUserEntity;

readonly class CredentialRegistered implements Event
{
    public function __construct(
        public PublicKeyCredentialUserEntity $userEntity,
        public CredentialRecord $credentialRecord,
    ) {}
}
