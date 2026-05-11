<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Database;

use Meteia\Database\DatabaseTables;
use Meteia\WebAuthn\Configuration\WebAuthnUsersTable;
use Meteia\WebAuthn\Contracts\UserEntityRepository;
use Override;
use Webauthn\PublicKeyCredentialUserEntity;

readonly class PdoUserEntityRepository implements UserEntityRepository
{
    public function __construct(
        private DatabaseTables $db,
        private WebAuthnUsersTable $table,
    ) {}

    #[Override]
    public function findOneByUsername(string $username): ?PublicKeyCredentialUserEntity
    {
        $rows = $this->db->select((string) $this->table, ['username' => $username]);
        if ($rows === []) {
            return null;
        }
        $row = reset($rows);
        \assert(\is_object($row));

        return $this->hydrate($row);
    }

    #[Override]
    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
    {
        $rows = $this->db->select((string) $this->table, ['user_handle' => $userHandle]);
        if ($rows === []) {
            return null;
        }
        $row = reset($rows);
        \assert(\is_object($row));

        return $this->hydrate($row);
    }

    #[Override]
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
        \assert(\is_string($row->username) && \is_string($row->user_handle) && \is_string($row->display_name));

        return PublicKeyCredentialUserEntity::create($row->username, $row->user_handle, $row->display_name);
    }
}
