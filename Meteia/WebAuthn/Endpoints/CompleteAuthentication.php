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
use Meteia\WebAuthn\Errors\AssertionFailed;
use Meteia\WebAuthn\Errors\UnknownCredential;
use Meteia\WebAuthn\Events\AuthenticationSucceeded;
use Meteia\WebAuthn\RelyingParty;
use Meteia\WebAuthn\WebAuthnServer;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\PublicKeyCredentialRequestOptions;

readonly class CompleteAuthentication implements Endpoint
{
    public function __construct(
        private WebAuthnServer $webAuthnServer,
        private ChallengeCookie $challengeCookie,
        private CredentialSourceRepository $credentials,
        private RelyingParty $relyingParty,
        private EventDispatcher $events,
    ) {}

    #[Override]
    public function response(ServerRequestInterface $request): ResponseInterface
    {
        $body = (string) $request->getBody();
        $challenge = $this->challengeCookie->unseal(ChallengePurpose::Authentication, new RequestCookies($request));

        $publicKeyCredential = $this->webAuthnServer->deserializePublicKeyCredential($body);
        $response = $publicKeyCredential->response;
        if (!$response instanceof AuthenticatorAssertionResponse) {
            throw new AssertionFailed('Expected an AuthenticatorAssertionResponse');
        }

        $credentialRecord = $this->credentials->findOneByCredentialId($publicKeyCredential->rawId);
        if ($credentialRecord === null) {
            throw new UnknownCredential('No credential record found for the provided credential id');
        }

        $userHandle = $response->userHandle;
        $options = PublicKeyCredentialRequestOptions::create($challenge, rpId: $this->relyingParty->id());

        $updatedRecord = $this->webAuthnServer->verifyAssertion(
            $credentialRecord,
            $response,
            $options,
            $this->relyingParty->id(),
            $userHandle,
        );

        $this->credentials->saveCredentialRecord($updatedRecord);
        $this->events->dispatch(new AuthenticationSucceeded($updatedRecord, $userHandle));

        return new JsonResponse(['status' => 'ok']);
    }
}
