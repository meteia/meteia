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
use Meteia\WebAuthn\Contracts\UserEntityRepository;
use Meteia\WebAuthn\WebAuthnServer;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class BeginAuthentication implements Endpoint
{
    private const int CHALLENGE_TTL_SECONDS = 300;

    public function __construct(
        private WebAuthnServer $webAuthnServer,
        private ChallengeCookie $challengeCookie,
        private UserEntityRepository $users,
        private CredentialSourceRepository $credentials,
        private ResponseCookies $responseCookies,
    ) {}

    #[Override]
    public function response(ServerRequestInterface $request): ResponseInterface
    {
        $challenge = random_bytes(32);

        $allowCredentials = [];
        $payload = json_decode((string) $request->getBody(), true);
        $username = is_array($payload) && isset($payload['username']) ? (string) $payload['username'] : null;
        if ($username !== null) {
            $userEntity = $this->users->findOneByUsername($username);
            if ($userEntity !== null) {
                $allowCredentials = array_map(
                    static fn($record) => $record->getPublicKeyCredentialDescriptor(),
                    $this->credentials->findAllForUserEntity($userEntity),
                );
            }
        }

        $options = $this->webAuthnServer->requestOptions($challenge, $allowCredentials);

        $sealedCookie = $this->challengeCookie->seal(
            ChallengePurpose::Authentication,
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
