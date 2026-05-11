<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Challenge;

use Meteia\Cryptography\SecretKey\XChaCha20Poly1305;
use Meteia\Http\Cookies\OpenedCookie;
use Meteia\Http\Cookies\RequestCookies;
use Meteia\Http\Cookies\SealedCookie;
use Meteia\WebAuthn\Configuration\WebAuthnSecretKey;
use Meteia\WebAuthn\Errors\InvalidWebAuthnChallenge;
use RuntimeException;
use SodiumException;
use Throwable;

readonly class ChallengeCookie
{
    public function __construct(
        private XChaCha20Poly1305 $XChaCha20Poly1305,
        private WebAuthnSecretKey $secretKey,
    ) {}

    public function seal(ChallengePurpose $purpose, string $challengeBytes, int $secondsValid): SealedCookie
    {
        $expiresAt = time() + $secondsValid;
        try {
            $encoded = sodium_bin2base64($challengeBytes, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
        } catch (SodiumException $e) {
            throw new InvalidWebAuthnChallenge('Failed to encode challenge', previous: $e);
        }
        $payload = $expiresAt . '|' . $encoded;
        $opened = new OpenedCookie($purpose->cookieName(), $payload, $purpose->value);

        return $opened->seal($this->XChaCha20Poly1305, $this->secretKey)->sealedCookie;
    }

    public function unseal(ChallengePurpose $purpose, RequestCookies $cookies): string
    {
        try {
            $sealed = $cookies->sealed($purpose->cookieName());
        } catch (RuntimeException) {
            throw new InvalidWebAuthnChallenge('Challenge cookie missing');
        }

        try {
            $opened = $sealed->open($this->XChaCha20Poly1305, $this->secretKey);
        } catch (Throwable) {
            throw new InvalidWebAuthnChallenge('Challenge cookie invalid');
        }

        if ($opened->associatedData !== $purpose->value) {
            throw new InvalidWebAuthnChallenge('Challenge cookie purpose mismatch');
        }

        $parts = explode('|', $opened->value, 2);
        if (\count($parts) !== 2) {
            throw new InvalidWebAuthnChallenge('Challenge cookie malformed');
        }

        $expiresAt = $parts[0];
        $encodedChallenge = $parts[1] ?? '';
        if (time() > (int) $expiresAt) {
            throw new InvalidWebAuthnChallenge('Challenge expired');
        }

        try {
            return sodium_base642bin($encodedChallenge, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
        } catch (SodiumException) {
            throw new InvalidWebAuthnChallenge('Challenge cookie payload invalid');
        }
    }
}
