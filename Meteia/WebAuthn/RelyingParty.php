<?php

declare(strict_types=1);

namespace Meteia\WebAuthn;

use Meteia\WebAuthn\Configuration\RelyingPartyId;
use Meteia\WebAuthn\Configuration\RelyingPartyName;
use Meteia\WebAuthn\Configuration\RelyingPartyOrigins;
use Webauthn\PublicKeyCredentialRpEntity;

readonly class RelyingParty
{
    public PublicKeyCredentialRpEntity $entity;

    public function __construct(
        private RelyingPartyName $name,
        private RelyingPartyId $id,
        public RelyingPartyOrigins $origins,
    ) {
        // web-auth/webauthn-lib 5.3 deprecates the constructor `$name` arg
        // but its normalizer drops empty strings, so the spec-required
        // `name` key would be absent in the JSON sent to the browser. Pass
        // "" through the constructor (no deprecation) and assign the real
        // value directly to the public field.
        $this->entity = PublicKeyCredentialRpEntity::create('', id: (string) $this->id);
        $this->entity->name = (string) $this->name;
    }

    public function id(): string
    {
        return (string) $this->id;
    }

    public function name(): string
    {
        return (string) $this->name;
    }

    /**
     * @return list<string>
     */
    public function allowedOrigins(): array
    {
        return $this->origins->origins;
    }
}
