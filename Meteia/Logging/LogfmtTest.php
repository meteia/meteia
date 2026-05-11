<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Override;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class LogfmtTest extends TestCase
{
    private Logfmt $uut;

    #[Override]
    protected function setUp(): void
    {
        $this->uut = new Logfmt();
    }

    public function testSimpleMessage(): void
    {
        static::assertSame('level=info msg=world', $this->uut->format('info', 'world'));
    }

    public function testMessageWithContext(): void
    {
        static::assertSame('level=info msg=world one=1', $this->uut->format('info', 'world', ['one' => 1]));
    }

    public function testKeysAreSorted(): void
    {
        static::assertSame('alpha=1 level=info msg=world zero=zero', $this->uut->format('info', 'world', [
            'alpha' => 1,
            'zero' => 'zero',
        ]));
    }

    public function testZeroValuesAreNotDropped(): void
    {
        static::assertSame('level=info msg=world one=1 zero=0', $this->uut->format('info', 'world', [
            'one' => 1,
            'zero' => 0,
        ]));
    }

    public function testPrintsBoolAsTrueFalse(): void
    {
        static::assertSame('false=false level=info msg=world true=true', $this->uut->format('info', 'world', [
            'true' => true,
            'false' => false,
        ]));
    }

    public function testStringsWithSpacesAreQuoted(): void
    {
        static::assertSame('level=info msg=world special="four score"', $this->uut->format('info', 'world', [
            'special' => 'four score',
        ]));
    }

    public function testKeyNamesAreAsciiOnly(): void
    {
        static::assertSame('level=info msg=world z12=n', $this->uut->format('info', 'world', ['z1≥2' => 'n']));
    }

    public function testKeyNamesCanNotBeEmpty(): void
    {
        static::assertSame('level=info msg=world', $this->uut->format('info', 'world', ['≥' => 'n']));
    }

    public function testFloatsAreRounded(): void
    {
        static::assertSame('level=info msg=world num=3.1416', $this->uut->format('info', 'world', [
            'num' => 3.141_592_65,
        ]));
    }
}
