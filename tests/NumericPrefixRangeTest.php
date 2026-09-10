<?php

namespace alcamo\range;

use PHPUnit\Framework\TestCase;

use alcamo\exception\{OutOfRange, SyntaxError};

class NumericPrefixRangeTest extends TestCase
{
    /**
     * @dataProvider newFromStringProvider
     */
    public function testConstruct($min, $max, $expectedString): void {
        $range = new NumericPrefixRange($min, $max);

        $this->assertSame($expectedString, (string)$range);
    }

    public function newFromStringProvider(): array
    {
        return [
            [ null, null, '0-9' ],
            [ '', null, '0-9' ],
            [ '00', '', '0-9' ],
            [ null, '3', '0-3' ],
            [ '42', '', '42-99' ],
            [ '000', '000', '000' ],
            [ '00', '000', '000' ],
            [ '000', '00', '000-009' ],
            [ '99', '', '99' ],
            [ '12', '34', '12-34' ],
            [ '123', '4', '123-499' ],
            [ '1', '2345', '1000-2345' ]
        ];
    }

    public function testConstructException1(): void
    {
        $this->expectException(OutOfRange::class);
        $this->expectExceptionMessage(
            'Value "1" out of range ["2", "∞"]'
        );

        new NumericPrefixRange('2', '19');
    }

    public function testConstructException2(): void
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in "0a-9"; not a valid numeric prefix range'
        );

        new NumericPrefixRange('0a', '99');
    }
}
