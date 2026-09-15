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

    /**
     * @dataProvider touchesProvider
     */
    public function testTouches($range1, $range2, $expectedResult): void
    {
        $this->assertSame(
            $expectedResult,
            NumericPrefixRange::newFromString($range1)
                ->touches(NumericPrefixRange::newFromString($range2))
        );

        $this->assertSame(
            $expectedResult,
            NumericPrefixRange::newFromString($range2)
                ->touches(NumericPrefixRange::newFromString($range1))
        );
    }

    public function touchesProvider(): array
    {
        return [
            [ '', '', false ],
            [ '', '1-2', false ],
            [ '-123', '125-', false ],
            [ '-123', '124-', true ],
            [ '2-299', '3-411', true ],
            [ '5999', '6-7', true ],
            [ '-123', '1231-', false ]
        ];
    }

    /**
     * @dataProvider createUnionWithProvider
     */
    public function testCreateUnionWith($range1, $range2, $expectedUnion): void
    {
        if (!isset($expectedUnion)) {
            $this->assertNull(
                NumericPrefixRange::newFromString($range1)
                    ->createUnionWith(
                        NumericPrefixRange::newFromString($range2)
                    )
            );

            $this->assertNull(
                NumericPrefixRange::newFromString($range2)
                    ->createUnionWith(
                        NumericPrefixRange::newFromString($range1)
                    )
            );
        } else {
            $this->assertEquals(
                NumericPrefixRange::newFromString($expectedUnion),
                NumericPrefixRange::newFromString($range1)
                    ->createUnionWith(
                        NumericPrefixRange::newFromString($range2)
                    )
            );

            $this->assertEquals(
                NumericPrefixRange::newFromString($expectedUnion),
                NumericPrefixRange::newFromString($range2)
                    ->createUnionWith(
                        NumericPrefixRange::newFromString($range1)
                    )
            );
        }
    }

    public function createUnionWithProvider(): array
    {
        return [
            [ '', '', '' ],
            [ '', '1-', '' ],
            [ '', '-4567', '' ],
            [ '', '12-345', '' ],
            [ '', '98765', '' ],
            [ '123-', '-456', '' ],
            [ '123-', '456-', '123-' ],
            [ '123-', '456-7899', '123-' ],
            [ '123-', '456', '123-' ],
            [ '34-', '2', null ],
            [ '34-', '339', '339-' ],
            [ '5678-', '-568', '' ],
            [ '2345-', '-1', null ],
            [ '2345-', '-2344', '' ],
            [ '7-', '1234-5', null ],
            [ '6-', '1234-5', '1234-' ],
            [ '123-4567', '34-5', '123-5' ],
            [ '123-4567', '45', '123-45' ],
            [ '-765499', '7655-', '' ]
        ];
    }
}
