<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Commands;

use Meteia\Commands\Command;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredentialUserEntity;

readonly class RegisterCredential implements Command
{
    public function __construct(
        public PublicKeyCredentialUserEntity $userEntity,
        public CredentialRecord $credentialRecord,
    ) {}
}
