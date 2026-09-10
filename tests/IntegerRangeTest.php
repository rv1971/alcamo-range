<?php

namespace alcamo\range;

use PHPUnit\Framework\TestCase;
use alcamo\exception\{OutOfRange, SyntaxError};

class IntegerRangeTest extends TestCase
{
    /**
     * @dataProvider newFromStringProvider
     */
    public function testNewFromString(
        $str,
        $expectedMin,
        $expectedMax,
        $expectedString,
        $expectedIsDefined,
        $expectedIsBounded,
        $expectedIsExactValue
    ): void {
        $range = IntegerRange::newFromString($str);

        $this->assertEquals(
            new IntegerRange($expectedMin, $expectedMax),
            $range
        );

        $this->assertSame($expectedMin, $range->getMin());

        $this->assertSame($expectedMax, $range->getMax());

        $this->assertSame(
            [ $expectedMin, $expectedMax ],
            $range->getMinMax()
        );

        $this->assertSame($expectedString, (string)$range);

        $this->assertSame($expectedIsDefined, $range->isDefined());

        $this->assertSame($expectedIsBounded, $range->isBounded());

        $this->assertSame($expectedIsExactValue, $range->isExactValue());
    }

    public function newFromStringProvider(): array
    {
        return [
            'empty' => [ '', null, null, '', false, false, false ],
            'exact' => [ "  42\r\n", 42, 42, '42', true, true, true ],
            'left'  => [ '5 :', 5, null, '5:', true, false, false ],
            'right' => [ '-2  :  99', -2, 99, '-2:99', true, true, false ],
            'both'  => [ "-12\t:-7", -12, -7, '-12:-7', true, true, false ]
        ];
    }

    public function testNewFromStringException(): void
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in "-12:--8"; not a valid integer range'
        );

        IntegerRange::newFromString('-12:--8');
    }

    public function testConstructException2(): void
    {
        $this->expectException(OutOfRange::class);
        $this->expectExceptionMessage(
            'Value -2 out of range [1, "∞"]'
        );

        new IntegerRange(1, -2);
    }

    /**
     * @dataProvider containsProvider
     */
    public function testContains($range, $value, $expectedResult): void
    {
        $this->assertSame(
            $expectedResult,
            IntegerRange::newFromString($range)->contains($value)
        );
    }

    public function containsProvider(): array
    {
        return [
            'empty'   => [ '', 1, true ],
            'exact-1' => [ '77', 76, false ],
            'exact-2' => [ '77', 77, true ],
            'exact-3' => [ '77', 78, false ],
            'left-1'  => [ '5:', 4, false ],
            'left-2'  => [ '5:', 5, true ],
            'left-3'  => [ '5:', 6, true ],
            'right-1' => [ '-3:9', -3, true ],
            'right-2' => [ '-3:9', 9, true ],
            'right-3' => [ '-3:9', 10, false ],
            'right-4' => [ '-3:9', -4, false ],
            'both-1'  => [ '-30:20', 21, false ],
            'both-2'  => [ '-30:20', 20, true ],
            'both-3'  => [ '-30:20', -30, true ],
            'both-4'  => [ '-30:20', -31, false ]
        ];
    }

    /**
     * @dataProvider intersectsProvider
     */
    public function testIntersects($range1, $range2, $expectedResult): void
    {
        $this->assertSame(
            $expectedResult,
            IntegerRange::newFromString($range1)
                ->intersects(IntegerRange::newFromString($range2))
        );

        $this->assertSame(
            $expectedResult,
            IntegerRange::newFromString($range2)
                ->intersects(IntegerRange::newFromString($range1))
        );
    }

    public function intersectsProvider(): array
    {
        return [
            [ '', '', true ],
            [ '', '-1:', true ],
            [ '', ':2', true ],
            [ '', '-1:2', true ],
            [ '', '42', true ],
            [ '-1:', ':2', true ],
            [ '-1:', '2:', true ],
            [ '-1:', '2:3', true ],
            [ '-3:', '-1', true ],
            [ '-3:', '-4', false ],
            [ '2:', ':3', true ],
            [ '2:', ':1', false ],
            [ '2:', '-3:1', false ],
            [ ':2', ':-12', true ],
            [ ':2', '-2:-1', true ],
            [ ':2', '3:4', false ],
            [ ':-3', '-4', true ],
            [ ':-3', '-2', false ],
            [ '-3:-1', '-2:2', true ],
            [ '-3:-1', '0:2', false ],
            [ '-3:-1', '-2', true ],
            [ '-3:-2', '-4', false ],
            [ '-3:-2', '-1', false ]
        ];
    }

    public function testClassesDisjoint(): void
    {
        $this->assertFalse(
            (new IntegerRange(1, 2))->intersects(new NonNegativeRange(1, 2))
        );

        $this->assertFalse(
            (new IntegerRange(1, 2))->touches(new NonNegativeRange(3, 4))
        );

        $this->assertNull(
            (new IntegerRange(1, 2))
                ->createUnionWith(new NonNegativeRange(1, 2))
        );
    }

    /**
     * @dataProvider touchesProvider
     */
    public function testTouches($range1, $range2, $expectedResult): void
    {
        $this->assertSame(
            $expectedResult,
            IntegerRange::newFromString($range1)
                ->touches(IntegerRange::newFromString($range2))
        );

        $this->assertSame(
            $expectedResult,
            IntegerRange::newFromString($range2)
                ->touches(IntegerRange::newFromString($range1))
        );
    }

    public function touchesProvider(): array
    {
        return [
            [ '', '', false ],
            [ '', '-1:2', false ],
            [ ':-1', '-1:', false ],
            [ ':-2', '-1:', true ],
            [ '-5:-3', '-2:7', true ]
        ];
    }

    /**
     * @dataProvider createUnionProvider
     */
    public function testCreateUnion($range1, $range2, $expectedUnion): void
    {
        if (!isset($expectedUnion)) {
            $this->assertNull(
                IntegerRange::newFromString($range1)
                    ->createUnionWith(IntegerRange::newFromString($range2))
            );

            $this->assertNull(
                IntegerRange::newFromString($range2)
                    ->createUnionWith(IntegerRange::newFromString($range1))
            );
        } else {
            $this->assertEquals(
                IntegerRange::newFromString($expectedUnion),
                IntegerRange::newFromString($range1)
                    ->createUnionWith(IntegerRange::newFromString($range2))
            );

            $this->assertEquals(
                IntegerRange::newFromString($expectedUnion),
                IntegerRange::newFromString($range2)
                    ->createUnionWith(IntegerRange::newFromString($range1))
            );
        }
    }

    public function createUnionProvider(): array
    {
        return [
            [ '', '', '' ],
            [ '', '-1:', '' ],
            [ '', ':2', '' ],
            [ '', '-1:2', '' ],
            [ '', '42', '' ],
            [ '-1:', ':2', '' ],
            [ '-1:', '2:', '-1:' ],
            [ '-1:', '2:3', '-1:' ],
            [ '-3:', '-1', '-3:' ],
            [ '-3:', '-4', '-4:' ],
            [ '2:', ':3', '' ],
            [ '2:', ':1', '' ],
            [ '2:', '-3:1', '-3:' ],
            [ ':2', ':-12', ':2' ],
            [ ':2', '-2:-1', ':2' ],
            [ ':2', '3:4', ':4' ],
            [ ':-3', '-4', ':-3' ],
            [ ':-3', '-2', ':-2' ],
            [ '-3:-1', '-2:2', '-3:2' ],
            [ '-3:-1', '0:2', '-3:2' ],
            [ '-3:-1', '-2', '-3:-1' ],
            [ '-3:-2', '-4', '-4:-2' ],
            [ '-3:-2', '-1', '-3:-1' ],
            [ '-3:', '-5', null ],
            [ '3:', ':1', null ],
            [ '4:', '-3:1', null ],
            [ ':1', '3:4', null ],
            [ ':-4', '-2', null ],
            [ '-3:-1', '1:2', null ],
            [ '-3:-2', '-5', null ],
            [ '-4:-3', '-1', null ]
        ];
    }
}
