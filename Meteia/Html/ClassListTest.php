<?php

declare(strict_types=1);

namespace Meteia\Html;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ClassListTest extends TestCase
{
    public function testParsesWhitespaceSeparatedTokens(): void
    {
        static::assertSame('a b c', (string) ClassList::of('a   b  c'));
    }

    public function testDeduplicatesTokens(): void
    {
        static::assertSame('a b', (string) ClassList::of('a b a'));
    }

    public function testMergeUnionsTwoLists(): void
    {
        $merged = ClassList::of('a b')->merge(ClassList::of('b c'));

        static::assertSame('a b c', (string) $merged);
    }

    public function testAddIsImmutable(): void
    {
        $original = ClassList::of('a');
        (void) $original->add('b');

        static::assertSame('a', (string) $original);
    }
}
