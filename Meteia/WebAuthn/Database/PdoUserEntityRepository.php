<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Database;

use Meteia\Database\Database;
use Meteia\WebAuthn\Configuration\WebAuthnUsersTable;
use Meteia\WebAuthn\Contracts\UserEntityRepository;
use Webauthn\PublicKeyCredentialUserEntity;

readonly class PdoUserEntityRepository implements UserEntityRepository
{
    public function __construct(
        private Database $db,
        private WebAuthnUsersTable $table,
    ) {}

    #[\Override]
    public function findOneByUsername(string $username): ?PublicKeyCredentialUserEntity
    {
        $rows = $this->db->select((string) $this->table, ['username' => $username]);

        return $rows === [] ? null : $this->hydrate(reset($rows));
    }

    #[\Override]
    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
    {
        $rows = $this->db->select((string) $this->table, ['user_handle' => $userHandle]);

        return $rows === [] ? null : $this->hydrate(reset($rows));
    }

    #[\Override]
    public function saveUserEntity(PublicKeyCredentialUserEntity $userEntity): void
    {
        $this->db->upsert(
            (string) $this->table,
            [
                'username' => $userEntity->name,
                'display_name' => $userEntity->displayName,
            ],
            [
                'user_handle' => $userEntity->id,
            ],
        );
    }

    private function hydrate(object $row): PublicKeyCredentialUserEntity
    {
        return PublicKeyCredentialUserEntity::create($row->username, $row->user_handle, $row->display_name);
    }
}
