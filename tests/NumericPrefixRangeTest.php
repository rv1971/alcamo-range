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

    /**
     * @dataProvider getMatchProvider
     */
    public function testGetMatch($range, $text, $expectedMatch): void
    {
        $this->assertSame(
            $expectedMatch,
            NumericPrefixRange::newFromString($range)->getMatch($text)
        );
    }

    public function getMatchProvider(): array
    {
        return [
            [ '', '1234', '1' ],
            [ '', 'foo', null ],
            [ '1-23', '24', null ],
            [ '1-23', '2141', '21' ],
            [ '421-', '987654', '987' ],
            [ '420-', '41999', null ]
        ];
    }

    /**
     * @dataProvider toArrayProvider
     */
    public function testToArray($range, $expectedArray): void
    {
        $this->assertSame(
            $expectedArray,
            NumericPrefixRange::newFromString($range)->toArray()
        );
    }

    public function toArrayProvider(): array
    {
        return [
            [ '42', [ '42' ] ],
            [ '', [ '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ] ],
            [ '3-5', [ '3', '4', '5' ] ],
            [ '1234-1237', [ '1234', '1235', '1236', '1237' ] ],
            [
                '8709-88',
                [
                    '8709', '871', '872', '873', '874', '875',
                    '876', '877', '878', '879',
                    '88'
                ]
            ],
            [
                '709-8',
                [
                    '709', '71', '72', '73', '74', '75',
                    '76', '77', '78', '79',
                    '8'
                ]
            ],
            [
                '48-5122',
                [
                    '48', '49', '50',
                    '510', '511', '5120', '5121', '5122'
                ]
            ],
            [ '769-819', [ '769', '77', '78', '79', '80', '81' ] ],
            [
                '32-3234',
                [
                    '320', '321', '322',
                    '3230', '3231', '3232', '3233', '3234'
                ]
            ],
            [
                '836501-8365',
                [
                    '836501', '836502', '836503', '836504',
                    '836505', '836506', '836507', '836508', '836509',
                    '83651', '83652', '83653', '83654',
                    '83655', '83656', '83657', '83658', '83659'
                ]
            ]
        ];
    }
}
