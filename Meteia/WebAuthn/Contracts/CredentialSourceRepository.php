<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Contracts;

use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredentialUserEntity;

interface CredentialSourceRepository
{
    public function findOneByCredentialId(string $publicKeyCredentialId): ?CredentialRecord;

    /**
     * @return CredentialRecord[]
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $userEntity): array;

    public function saveCredentialRecord(CredentialRecord $credentialRecord): void;
}
