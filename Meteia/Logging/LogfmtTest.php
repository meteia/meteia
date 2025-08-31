<?php

declare(strict_types=1);

use Meteia\Logging\Logfmt;

beforeEach(function (): void {
    $this->uut = new Logfmt();
});

test('simple message', function (): void {
    $out = $this->uut->format('info', 'world');

    $this->assertEquals('level=info msg=world', $out);
});

test('message with context', function (): void {
    $out = $this->uut->format('info', 'world', ['one' => 1]);

    $this->assertEquals('level=info msg=world one=1', $out);
});

test('keys are sorted', function (): void {
    $out = $this->uut->format('info', 'world', ['alpha' => 1, 'zero' => 'zero']);

    $this->assertEquals('alpha=1 level=info msg=world zero=zero', $out);
});

test('zero-values are not-dropped', function (): void {
    $out = $this->uut->format('info', 'world', ['one' => 1, 'zero' => 0]);

    $this->assertEquals('level=info msg=world one=1 zero=0', $out);
});

test('prints bool as 0/1', function (): void {
    $out = $this->uut->format('info', 'world', ['true' => true, 'false' => false]);

    $this->assertEquals('false=false level=info msg=world true=true', $out);
});

test('strings with spaces are quoted', function (): void {
    $out = $this->uut->format('info', 'world', ['special' => 'four score']);

    $this->assertEquals('level=info msg=world special="four score"', $out);
});

test('key names are ASCII only', function (): void {
    $out = $this->uut->format('info', 'world', ['z1≥2' => 'n']);

    $this->assertEquals('level=info msg=world z12=n', $out);
});

test('key names can not be empty', function (): void {
    $out = $this->uut->format('info', 'world', ['≥' => 'n']);

    $this->assertEquals('level=info msg=world', $out);
});

test('floats are rounded', function (): void {
    $out = $this->uut->format('info', 'world', ['num' => 3.14159265]);

    $this->assertEquals('level=info msg=world num=3.1416', $out);
});

// test('strings with quotes are escaped', function () {
//    $out = $this->>uut->format("info", "world", ['special' => 'four"score']);

//    /** @var \PHPUnit\Framework\TestCase $this */
//    $this->assertEquals('level=info msg=world special=four\"score', $out);
// });

// test('strings with spaces & quotes are quoted & escaped', function () {
//    $out = $this->>uut->format("info", "world", ['special' => 'four "score']);

//    /** @var \PHPUnit\Framework\TestCase $this */
//    $this->assertEquals('level=info msg=world special="four \"score"', $out);
// });
