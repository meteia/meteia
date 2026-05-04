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
final class HtmlEncoderTest extends TestCase
{
    public function testVoidTagSelfCloses(): void
    {
        static::assertSame('<img src="/x.png" />', (string) el('img', ['src' => '/x.png']));
    }

    public function testEscapesAttributeValues(): void
    {
        static::assertSame('<a title="&quot;hi&quot;"></a>', (string) el('a', ['title' => '"hi"']));
    }

    public function testBooleanTrueRendersAttributeName(): void
    {
        static::assertSame('<input disabled />', (string) el('input', ['disabled' => true]));
    }

    public function testEmptyAttributeIsOmitted(): void
    {
        static::assertSame('<div></div>', (string) el('div', ['class' => '']));
    }

    public function testClassNameIsRewrittenToClass(): void
    {
        static::assertSame('<div class="card"></div>', (string) el('div', ['className' => 'card']));
    }

    public function testNestedTagIsEncodedRecursively(): void
    {
        static::assertSame('<div><span>x</span></div>', (string) el('div', [], el('span', [], 'x')));
    }

    public function testComponentChildIsRenderedRecursively(): void
    {
        $component = new class implements Component {
            #[\Override]
            public function render(): Node
            {
                return el('span', [], 'x');
            }
        };

        static::assertSame('<div><span>x</span></div>', new HtmlEncoder()->encode(el('div', [], $component)));
    }
}
