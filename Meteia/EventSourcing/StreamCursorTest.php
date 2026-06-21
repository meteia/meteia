<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tuupola\Base62;

/**
 * @internal
 */
final class StreamCursorTest extends TestCase
{
    public function testTokenRoundTripPreservesPosition(): void
    {
        $cursor = StreamCursor::after(new StreamVersion(42));

        $restored = StreamCursor::fromToken($cursor->token());

        StreamCursorTest::assertSame(42, $restored->lowerBoundExclusive());
        StreamCursorTest::assertTrue($restored->version()->equalTo(new StreamVersion(42)));
    }

    public function testTokenIsOpaqueAndPrefixed(): void
    {
        $token = StreamCursor::after(new StreamVersion(7))->token();

        StreamCursorTest::assertStringStartsWith('evc_', $token);
        StreamCursorTest::assertStringNotContainsString('7', substr($token, 4));
    }

    public function testCursorSerializesToItsTokenAsJson(): void
    {
        $cursor = StreamCursor::after(new StreamVersion(3));

        StreamCursorTest::assertSame($cursor->token(), $cursor->jsonSerialize());
        StreamCursorTest::assertSame($cursor->token(), (string) $cursor);
    }

    public function testRejectsTokenWithoutCursorPrefix(): void
    {
        $this->expectException(InvalidArgumentException::class);
        StreamCursor::fromToken('cur_whatever');
    }

    public function testRejectsTokenThatDoesNotDecodeToAPosition(): void
    {
        $token = 'evc_' . new Base62()->encode('not-a-number');

        $this->expectException(InvalidArgumentException::class);
        StreamCursor::fromToken($token);
    }
}
