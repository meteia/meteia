<?php

declare(strict_types=1);

namespace Meteia\Authentication\Oauth;

use Carbon\Carbon;

class AccessToken
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $tokenType,
        public readonly \DateTimeInterface $expires,
        public readonly string $refreshToken,
        public readonly array $scopes,
    ) {
    }

    public static function fromJsonString(string $json): self
    {
        $r = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        $expires = (new \DateTime())->modify("+{$r->expires_in} seconds");

        return new self($r->access_token, $r->token_type, $expires, $r->refresh_token, $r->scope);
    }

    public function hasScope(string $scope): bool
    {
        return \in_array($scope, $this->scopes, true);
    }

    // public function shouldRenew(): bool {
    //    Carbon::create($this->expires)->isAfter()
    // }
}
