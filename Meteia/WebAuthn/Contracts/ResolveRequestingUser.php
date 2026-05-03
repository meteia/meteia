<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Contracts;

use Psr\Http\Message\ServerRequestInterface;
use Webauthn\PublicKeyCredentialUserEntity;

interface ResolveRequestingUser
{
    public function fromRequest(ServerRequestInterface $request): PublicKeyCredentialUserEntity;
}
