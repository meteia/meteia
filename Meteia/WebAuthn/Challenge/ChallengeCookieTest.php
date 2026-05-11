<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Challenge;

use Meteia\Cryptography\SecretKey\XChaCha20Poly1305;
use Meteia\Http\Cookies\RequestCookies;
use Meteia\WebAuthn\Configuration\WebAuthnSecretKey;
use Meteia\WebAuthn\Errors\InvalidWebAuthnChallenge;
use Nyholm\Psr7\ServerRequest;
use Override;
use PHPUnit\Framework\TestCase;
use Tuupola\Base62;

class ChallengeCookieTest extends TestCase
{
    private ChallengeCookie $uut;

    #[Override]
    protected function setUp(): void
    {
        $crypto = new XChaCha20Poly1305(new Base62());
        $secret = WebAuthnSecretKey::random();
        $this->uut = new ChallengeCookie($crypto, $secret);
    }

    public function testRoundTripRecoversChallengeBytes(): void
    {
        $challenge = random_bytes(32);
        $sealed = $this->uut->seal(ChallengePurpose::Registration, $challenge, 60);

        $recovered = $this->uut->unseal(ChallengePurpose::Registration, $this->cookies($sealed->name, $sealed->value));

        $this->assertSame($challenge, $recovered);
    }

    public function testExpiredCookieIsRejected(): void
    {
        $sealed = $this->uut->seal(ChallengePurpose::Registration, random_bytes(32), -1);

        $this->expectException(InvalidWebAuthnChallenge::class);
        $this->uut->unseal(ChallengePurpose::Registration, $this->cookies($sealed->name, $sealed->value));
    }

    public function testPurposeMismatchIsRejected(): void
    {
        $sealed = $this->uut->seal(ChallengePurpose::Registration, random_bytes(32), 60);

        $this->expectException(InvalidWebAuthnChallenge::class);
        $this->uut->unseal(ChallengePurpose::Authentication, $this->cookies(
            ChallengePurpose::Authentication->cookieName(),
            $sealed->value,
        ));
    }

    public function testMissingCookieIsRejected(): void
    {
        $request = new ServerRequest('POST', '/');

        $this->expectException(InvalidWebAuthnChallenge::class);
        $this->uut->unseal(ChallengePurpose::Registration, new RequestCookies($request));
    }

    public function testTamperedCiphertextIsRejected(): void
    {
        $sealed = $this->uut->seal(ChallengePurpose::Registration, random_bytes(32), 60);
        $tampered = strtr($sealed->value, ['A' => 'B', 'a' => 'b', '0' => '1']);

        $this->expectException(InvalidWebAuthnChallenge::class);
        $this->uut->unseal(ChallengePurpose::Registration, $this->cookies($sealed->name, $tampered));
    }

    private function cookies(string $name, string $value): RequestCookies
    {
        $request = new ServerRequest('POST', '/');
        $request = $request->withCookieParams([$name => $value]);

        return new RequestCookies($request);
    }
}
