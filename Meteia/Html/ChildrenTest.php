<?php

declare(strict_types=1);

namespace Meteia\Html;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ChildrenTest extends TestCase
{
    public function testWhenFalseDoesNotInvokeProducer(): void
    {
        $invoked = false;
        (void) Children::of()->when(false, static function () use (&$invoked): string {
            $invoked = true;

            return 'x';
        });

        static::assertFalse($invoked);
    }

    public function testWhenTrueAppendsProducedNode(): void
    {
        $children = Children::of('a')->when(true, static fn(): string => 'b');

        static::assertSame('a' . \PHP_EOL . 'b', (string) $children);
    }

    public function testEachMapsItems(): void
    {
        $children = Children::of()->each(['a', 'b', 'c'], static fn(string $v): string => "<i>{$v}</i>");

        static::assertSame('<i>a</i>' . \PHP_EOL . '<i>b</i>' . \PHP_EOL . '<i>c</i>', (string) $children);
    }

    public function testAppendIsImmutable(): void
    {
        $original = Children::of('a');
        (void) $original->append('b');

        static::assertSame('a', (string) $original);
    }
}
