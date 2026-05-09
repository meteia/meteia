<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing;

use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\ProcessId;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AmbientMessageScopeSourceTest extends TestCase
{
    public function testReturnsTheDefaultScopeOutsideOfUsing(): void
    {
        $default = $this->scope();
        $source = new AmbientMessageScopeSource($default);

        static::assertSame($default, $source->current());
    }

    public function testReturnsThePushedScopeInsideUsing(): void
    {
        $default = $this->scope();
        $pushed = $this->scope();
        $source = new AmbientMessageScopeSource($default);

        $observed = $source->using($pushed, static fn(): MessageScope => $source->current());

        static::assertSame($pushed, $observed);
    }

    public function testRestoresThePreviousScopeAfterUsing(): void
    {
        $default = $this->scope();
        $source = new AmbientMessageScopeSource($default);

        $source->using($this->scope(), static fn(): null => null);

        static::assertSame($default, $source->current());
    }

    public function testRestoresPreviousScopeEvenIfTheBodyThrows(): void
    {
        $default = $this->scope();
        $source = new AmbientMessageScopeSource($default);

        try {
            $source->using($this->scope(), static fn(): never => throw new \RuntimeException('boom'));
        } catch (\RuntimeException) {
        }

        static::assertSame($default, $source->current());
    }

    public function testNestedUsingRestoresInnerScopeOnReturn(): void
    {
        $default = $this->scope();
        $outer = $this->scope();
        $inner = $this->scope();
        $source = new AmbientMessageScopeSource($default);

        $observed = $source->using($outer, static fn(): MessageScope => $source->using($inner, static fn(): MessageScope => $source->current()));

        static::assertSame($inner, $observed);
        static::assertSame($default, $source->current());
    }

    private function scope(): MessageScope
    {
        return new MessageScope(CorrelationId::random(), CausationId::random(), ProcessId::random());
    }
}
