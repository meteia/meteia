<?php

declare(strict_types=1);

namespace Meteia\Html;

use PHPUnit\Framework\TestCase;

use function Meteia\Html\Elements\el;

/**
 * @internal
 *
 * @coversNothing
 */
final class TagTest extends TestCase
{
    public function testWithReturnsNewInstance(): void
    {
        $original = el('div');
        $withChild = $original->with('hello');

        static::assertNotSame($original, $withChild);
    }

    public function testWithDoesNotMutateOriginal(): void
    {
        $original = el('div');
        (void) $original->with('hello');

        static::assertSame('<div></div>', (string) $original);
    }

    public function testEncodesChildrenInOrder(): void
    {
        static::assertSame('<div>a' . \PHP_EOL . 'b</div>', (string) el('div', [], 'a', 'b'));
    }

    public function testWithClassMergesIntoExistingClassAttribute(): void
    {
        $tag = el('div', ['class' => 'a'])->withClass('b');

        static::assertSame('<div class="a b"></div>', (string) $tag);
    }

    public function testWithAttrAppendsAttribute(): void
    {
        $tag = el('a')->withAttr('href', '/x');

        static::assertSame('<a href="/x"></a>', (string) $tag);
    }

    public function testShorthandClassStringPromotesToClassAttribute(): void
    {
        static::assertSame('<div class="card"></div>', (string) el('div', ['card']));
    }
}
