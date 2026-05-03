<?php

declare(strict_types=1);

namespace Meteia\WebAuthn;

use Cose\Algorithms;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialUserEntity;

readonly class WebAuthnServer
{
    public function __construct(
        public RelyingParty $relyingParty,
        public SerializerInterface $serializer,
        private AuthenticatorAttestationResponseValidator $attestationValidator,
        private AuthenticatorAssertionResponseValidator $assertionValidator,
    ) {}

    /**
     * @param list<PublicKeyCredentialDescriptor> $excludeCredentials
     * @param list<PublicKeyCredentialParameters> $publicKeyCredentialParameters
     */
    public function creationOptions(
        PublicKeyCredentialUserEntity $user,
        string $challenge,
        array $excludeCredentials = [],
        array $publicKeyCredentialParameters = [],
    ): PublicKeyCredentialCreationOptions {
        if ($publicKeyCredentialParameters === []) {
            $publicKeyCredentialParameters = [
                PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ES256),
                PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_RS256),
            ];
        }

        return PublicKeyCredentialCreationOptions::create(
            $this->relyingParty->entity,
            $user,
            $challenge,
            $publicKeyCredentialParameters,
            excludeCredentials: $excludeCredentials,
        );
    }

    /**
     * @param list<PublicKeyCredentialDescriptor> $allowCredentials
     */
    public function requestOptions(string $challenge, array $allowCredentials = []): PublicKeyCredentialRequestOptions
    {
        return PublicKeyCredentialRequestOptions::create(
            $challenge,
            rpId: $this->relyingParty->id(),
            allowCredentials: $allowCredentials,
        );
    }

    public function deserializePublicKeyCredential(string $json): PublicKeyCredential
    {
        return $this->serializer->deserialize($json, PublicKeyCredential::class, 'json');
    }

    public function verifyAttestation(
        AuthenticatorAttestationResponse $response,
        PublicKeyCredentialCreationOptions $options,
        string $host,
    ): CredentialRecord {
        return $this->attestationValidator->check($response, $options, $host);
    }

    public function verifyAssertion(
        CredentialRecord $credentialRecord,
        AuthenticatorAssertionResponse $response,
        PublicKeyCredentialRequestOptions $options,
        string $host,
        ?string $userHandle,
    ): CredentialRecord {
        return $this->assertionValidator->check($credentialRecord, $response, $options, $host, $userHandle);
    }
}
