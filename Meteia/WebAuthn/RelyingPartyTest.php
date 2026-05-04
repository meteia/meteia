<?php

declare(strict_types=1);

namespace Meteia\WebAuthn;

use Meteia\WebAuthn\Configuration\RelyingPartyId;
use Meteia\WebAuthn\Configuration\RelyingPartyName;
use Meteia\WebAuthn\Configuration\RelyingPartyOrigins;
use PHPUnit\Framework\TestCase;

class RelyingPartyTest extends TestCase
{
    public function testEntityCarriesNameAndId(): void
    {
        $relyingParty = new RelyingParty(
            new RelyingPartyName('Example'),
            new RelyingPartyId('example.com'),
            new RelyingPartyOrigins('https://example.com'),
        );

        static::assertSame('Example', $relyingParty->entity->name);
        static::assertSame('example.com', $relyingParty->entity->id);
        static::assertSame('example.com', $relyingParty->id());
        static::assertSame('Example', $relyingParty->name());
        static::assertSame(['https://example.com'], $relyingParty->allowedOrigins());
    }
}
