<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Commands;

use Meteia\Commands\Command;
use Webauthn\CredentialRecord;

/**
 * @implements Command<void>
 */
readonly class RecordSuccessfulAuthentication implements Command
{
    public function __construct(
        public CredentialRecord $credentialRecord,
        public ?string $userHandle,
    ) {}
}
