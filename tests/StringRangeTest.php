<?php

namespace alcamo\range;

use PHPUnit\Framework\TestCase;
use alcamo\exception\{OutOfRange, SyntaxError};

class StringRangeTest extends TestCase
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
        $range = StringRange::newFromString($str);

        $this->assertEquals(
            new StringRange($expectedMin, $expectedMax),
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
            'undefined1' => [ '', '', null, '', false, false, false ],
            'undefined2' => [ '-', '', null, '', false, false, false ],
            'exact' => [ 'foo', 'foo', 'foo', 'foo', true, true, true ],
            'left'  => [ 'foo-', 'foo', null, 'foo-', true, false, false ],
            'right' => [ '-bar', '', 'bar', '-bar', true, true, false ],
            'both'  => [ 'bar-foo', 'bar', 'foo', 'bar-foo', true, true, false ]
        ];
    }

    public function testConstructException1(): void
    {
        $this->expectException(OutOfRange::class);
        $this->expectExceptionMessage(
            'Value "bar" out of range ["foo", "∞"]'
        );

        new StringRange('foo', 'bar');
    }

    /**
     * @dataProvider containsProvider
     */
    public function testContains($range, $value, $expectedResult): void
    {
        $this->assertSame(
            $expectedResult,
            StringRange::newFromString($range)->contains($value)
        );
    }

    public function containsProvider(): array
    {
        return [
            'empty'   => [ '', 'foo', true ],
            'exact-1' => [ 'foo', 'bar', false ],
            'exact-2' => [ 'foo', 'foo', true ],
            'left-1'  => [ 'foo-', 'fo', false ],
            'left-2'  => [ 'foo-', 'foo', true ],
            'left-3'  => [ 'foo-', 'fooo', true ],
            'left-4'  => [ 'foo-', 'fop', true ],
            'right-1' => [ '-foo', '', true ],
            'right-2' => [ '-foo', 'foo', true ],
            'right-3' => [ '-foo', 'fooo', false ],
            'both-1'  => [ 'bar-foo', 'ba', false ],
            'both-2'  => [ 'bar-foo', 'bar', true ],
            'both-3'  => [ 'bar-foo', 'foo', true ],
            'both-4'  => [ 'bar-foo', 'fooo', false ],
            'both-5'  => [ 'bar-foo', 'fop', false ]
        ];
    }
}
