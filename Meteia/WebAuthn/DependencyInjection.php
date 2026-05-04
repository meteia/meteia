<?php

declare(strict_types=1);

use Meteia\Configuration\Configuration;
use Meteia\WebAuthn\Configuration\RelyingPartyId;
use Meteia\WebAuthn\Configuration\RelyingPartyOrigins;
use Meteia\WebAuthn\Configuration\WebAuthnCredentialsTable;
use Meteia\WebAuthn\Configuration\WebAuthnSecretKey;
use Meteia\WebAuthn\Configuration\WebAuthnUsersTable;
use Meteia\WebAuthn\Contracts\CredentialSourceRepository;
use Meteia\WebAuthn\Contracts\EventDispatcher;
use Meteia\WebAuthn\Contracts\UserEntityRepository;
use Meteia\WebAuthn\Database\PdoCredentialSourceRepository;
use Meteia\WebAuthn\Database\PdoUserEntityRepository;
use Meteia\WebAuthn\NullEventDispatcher;
use Meteia\WebAuthn\RelyingParty;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;

return [
    RelyingPartyId::class =>
        static fn(Configuration $configuration): RelyingPartyId => new RelyingPartyId($configuration->string(
            'WEBAUTHN_RP_ID',
            'localhost',
        )),
    RelyingPartyOrigins::class =>
        static fn(Configuration $configuration): RelyingPartyOrigins => RelyingPartyOrigins::fromCsv($configuration->string(
            'WEBAUTHN_RP_ORIGINS',
            'https://localhost',
        )),
    WebAuthnSecretKey::class => static function (Configuration $configuration): WebAuthnSecretKey {
        $value = $configuration->string('WEBAUTHN_SECRET_KEY', '');
        if ($value === '') {
            throw new Exception('WEBAUTHN_SECRET_KEY not set');
        }

        return WebAuthnSecretKey::fromToken($value);
    },
    WebAuthnCredentialsTable::class =>
        static fn(Configuration $configuration): WebAuthnCredentialsTable => new WebAuthnCredentialsTable($configuration->string(
            'WEBAUTHN_CREDENTIALS_TABLE',
            'webauthn_credentials',
        )),
    WebAuthnUsersTable::class =>
        static fn(Configuration $configuration): WebAuthnUsersTable => new WebAuthnUsersTable($configuration->string(
            'WEBAUTHN_USERS_TABLE',
            'webauthn_users',
        )),
    AttestationStatementSupportManager::class =>
        static fn(): AttestationStatementSupportManager => new AttestationStatementSupportManager([new NoneAttestationStatementSupport()]),
    SerializerInterface::class =>
        static fn(AttestationStatementSupportManager $manager): SerializerInterface => new WebauthnSerializerFactory(
            $manager,
        )->create(),
    CeremonyStepManagerFactory::class => static function (RelyingParty $relyingParty): CeremonyStepManagerFactory {
        $factory = new CeremonyStepManagerFactory();
        $factory->setAllowedOrigins($relyingParty->allowedOrigins());

        return $factory;
    },
    AuthenticatorAttestationResponseValidator::class =>
        static fn(CeremonyStepManagerFactory $factory): AuthenticatorAttestationResponseValidator => AuthenticatorAttestationResponseValidator::create(
            $factory->creationCeremony(),
        ),
    AuthenticatorAssertionResponseValidator::class =>
        static fn(CeremonyStepManagerFactory $factory): AuthenticatorAssertionResponseValidator => AuthenticatorAssertionResponseValidator::create(
            $factory->requestCeremony(),
        ),
    CredentialSourceRepository::class => PdoCredentialSourceRepository::class,
    UserEntityRepository::class => PdoUserEntityRepository::class,
    EventDispatcher::class => NullEventDispatcher::class,
];
