<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Endpoints;

use Meteia\Http\Cookies\RequestCookies;
use Meteia\Http\Endpoint;
use Meteia\Http\Responses\JsonResponse;
use Meteia\WebAuthn\Challenge\ChallengeCookie;
use Meteia\WebAuthn\Challenge\ChallengePurpose;
use Meteia\WebAuthn\Contracts\CredentialSourceRepository;
use Meteia\WebAuthn\Contracts\EventDispatcher;
use Meteia\WebAuthn\Contracts\ResolveRequestingUser;
use Meteia\WebAuthn\Errors\AttestationFailed;
use Meteia\WebAuthn\Events\CredentialRegistered;
use Meteia\WebAuthn\RelyingParty;
use Meteia\WebAuthn\WebAuthnServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredentialCreationOptions;

readonly class CompleteRegistration implements Endpoint
{
    public function __construct(
        private WebAuthnServer $webAuthnServer,
        private ChallengeCookie $challengeCookie,
        private CredentialSourceRepository $credentials,
        private ResolveRequestingUser $resolveRequestingUser,
        private RelyingParty $relyingParty,
        private EventDispatcher $events,
    ) {}

    #[\Override]
    public function response(ServerRequestInterface $request): ResponseInterface
    {
        $body = (string) $request->getBody();
        $userEntity = $this->resolveRequestingUser->fromRequest($request);
        $challenge = $this->challengeCookie->unseal(ChallengePurpose::Registration, new RequestCookies($request));

        $publicKeyCredential = $this->webAuthnServer->deserializePublicKeyCredential($body);
        $response = $publicKeyCredential->response;
        if (!$response instanceof AuthenticatorAttestationResponse) {
            throw new AttestationFailed('Expected an AuthenticatorAttestationResponse');
        }

        $options = PublicKeyCredentialCreationOptions::create($this->relyingParty->entity, $userEntity, $challenge);

        $credentialRecord = $this->webAuthnServer->verifyAttestation($response, $options, $this->relyingParty->id());

        $this->credentials->saveCredentialRecord($credentialRecord);
        $this->events->dispatch(new CredentialRegistered($userEntity, $credentialRecord));

        return new JsonResponse(['status' => 'ok']);
    }
}
