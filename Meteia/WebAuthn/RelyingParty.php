<?php

declare(strict_types=1);

namespace Meteia\WebAuthn;

use Meteia\WebAuthn\Configuration\RelyingPartyId;
use Meteia\WebAuthn\Configuration\RelyingPartyOrigins;
use Webauthn\PublicKeyCredentialRpEntity;

readonly class RelyingParty
{
    public PublicKeyCredentialRpEntity $entity;

    public function __construct(
        private RelyingPartyId $id,
        public RelyingPartyOrigins $origins,
    ) {
        $this->entity = PublicKeyCredentialRpEntity::create(id: (string) $this->id);
    }

    public function id(): string
    {
        return (string) $this->id;
    }

    /**
     * @return list<string>
     */
    public function allowedOrigins(): array
    {
        return $this->origins->origins;
    }
}
