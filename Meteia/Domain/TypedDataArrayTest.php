<?php

declare(strict_types=1);

use Meteia\Domain\TypedDataArray;
use Meteia\Domain\ValueObjects\Money\PreciseUSD;

return;
it('returns values', function (): void {
    // Arrange
    $expected = [
        'an_array' => ['one', 'two'],
        'a_string' => 'yup',
        'an_int' => 100,
        'a_bool' => '1',
        'a_float' => 3.14159,
        'usd' => 3.14159,
    ];

    // Act
    $data = new TypedDataArray($expected);

    // Assert
    $this->assertEquals($data->string('a_string', 'invalid'), $expected['a_string']);
    $this->assertEquals($data->int('an_int', 0), $expected['an_int']);
    $this->assertTrue($data->boolean('a_bool', false));
    $this->assertEquals($data->array('an_array', []), $expected['an_array']);
    $this->assertEquals($data->float('a_float', 2.71), $expected['a_float']);
    $this->assertTrue($data->preciseUSD('usd', 2.71)->equalTo(new PreciseUSD(3.14159)));

    $this->assertEquals($data->all(), $expected);
});
