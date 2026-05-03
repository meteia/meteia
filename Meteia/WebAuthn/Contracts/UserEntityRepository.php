<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Contracts;

use Webauthn\PublicKeyCredentialUserEntity;

interface UserEntityRepository
{
    public function findOneByUsername(string $username): ?PublicKeyCredentialUserEntity;

    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity;

    public function saveUserEntity(PublicKeyCredentialUserEntity $userEntity): void;
}
