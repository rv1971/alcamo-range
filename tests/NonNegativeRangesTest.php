<?php

namespace alcamo\range;

use PHPUnit\Framework\TestCase;

class NonNegativeRangesTest extends TestCase
{
    /**
     * @dataProvider newFromRangeIterableProvider
     */
    public function testNewFromRangeIterable($rangeStrings, $expectedStr): void
    {
        $ranges = NonNegativeRanges::newFromRangeIterable($rangeStrings);

        $this->assertSame($expectedStr, (string)$ranges);

        foreach ($ranges as $key => $range) {
            $this->assertSame($key, (string)$range);
        }

        shuffle($rangeStrings);

        $ranges = NonNegativeRanges::newFromRangeIterable($rangeStrings);

        $this->assertSame($expectedStr, (string)$ranges);
    }

    public function newFromRangeIterableProvider(): array
    {
        return [
            [ [], '' ],
            [ [ '1-2', '-' ], '-' ],
            [ [ '-1', '2-' ], '-' ],
            [ [ '-2', '-3', '5-8' ], '0-3 5-8' ]
        ];
    }
}
