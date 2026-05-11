<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Endpoints;

use DateTimeImmutable;
use Laminas\Diactoros\Response\TextResponse;
use Meteia\Http\Cookies\SameSite;
use Meteia\Http\Endpoint;
use Meteia\Http\Middleware\ResponseCookies;
use Meteia\WebAuthn\Challenge\ChallengeCookie;
use Meteia\WebAuthn\Challenge\ChallengePurpose;
use Meteia\WebAuthn\Contracts\CredentialSourceRepository;
use Meteia\WebAuthn\Contracts\ResolveRequestingUser;
use Meteia\WebAuthn\WebAuthnServer;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class BeginRegistration implements Endpoint
{
    private const int CHALLENGE_TTL_SECONDS = 300;

    public function __construct(
        private WebAuthnServer $webAuthnServer,
        private ChallengeCookie $challengeCookie,
        private ResolveRequestingUser $resolveRequestingUser,
        private CredentialSourceRepository $credentials,
        private ResponseCookies $responseCookies,
    ) {}

    #[Override]
    public function response(ServerRequestInterface $request): ResponseInterface
    {
        $userEntity = $this->resolveRequestingUser->fromRequest($request);
        $challenge = random_bytes(32);

        $excludeCredentials = array_values(array_map(
            static fn($record) => $record->getPublicKeyCredentialDescriptor(),
            $this->credentials->findAllForUserEntity($userEntity),
        ));

        $options = $this->webAuthnServer->creationOptions($userEntity, $challenge, $excludeCredentials);

        $sealedCookie = $this->challengeCookie->seal(
            ChallengePurpose::Registration,
            $challenge,
            self::CHALLENGE_TTL_SECONDS,
        );
        $this->responseCookies->add(
            $sealedCookie,
            new DateTimeImmutable('+' . self::CHALLENGE_TTL_SECONDS . ' seconds'),
            sameSite: SameSite::Strict,
        );

        $json = $this->webAuthnServer->serializer->serialize($options, 'json');

        return new TextResponse($json, 200, ['Content-Type' => 'application/json']);
    }
}
