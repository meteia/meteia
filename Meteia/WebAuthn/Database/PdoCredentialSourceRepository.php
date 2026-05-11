<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Database;

use Meteia\Database\DatabaseTables;
use Meteia\WebAuthn\Configuration\WebAuthnCredentialsTable;
use Meteia\WebAuthn\Contracts\CredentialSourceRepository;
use Override;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredentialUserEntity;

readonly class PdoCredentialSourceRepository implements CredentialSourceRepository
{
    public function __construct(
        private DatabaseTables $db,
        private WebAuthnCredentialsTable $table,
        private SerializerInterface $serializer,
    ) {}

    #[Override]
    public function findOneByCredentialId(string $publicKeyCredentialId): ?CredentialRecord
    {
        $rows = $this->db->select((string) $this->table, [
            'public_key_credential_id' => $publicKeyCredentialId,
        ]);

        return $rows === [] ? null : $this->hydrate(reset($rows));
    }

    #[Override]
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $userEntity): array
    {
        $rows = $this->db->select((string) $this->table, [
            'user_handle' => $userEntity->id,
        ]);

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function saveCredentialRecord(CredentialRecord $credentialRecord): void
    {
        $payload = $this->serializer->serialize($credentialRecord, 'json');
        $this->db->upsert(
            (string) $this->table,
            [
                'user_handle' => $credentialRecord->userHandle,
                'counter' => $credentialRecord->counter,
                'data' => $payload,
            ],
            [
                'public_key_credential_id' => $credentialRecord->publicKeyCredentialId,
            ],
        );
    }

    private function hydrate(object $row): CredentialRecord
    {
        return $this->serializer->deserialize($row->data, CredentialRecord::class, 'json');
    }
}
