<?php

namespace alcamo\range;

use PHPUnit\Framework\TestCase;

class IntegerRangesTest extends TestCase
{
    /**
     * @dataProvider newFromRangeIterableProvider
     */
    public function testNewFromRangeIterable($rangeStrings, $expectedStr): void
    {
        $ranges = IntegerRanges::newFromRangeIterable($rangeStrings);

        $this->assertSame($expectedStr, (string)$ranges);

        foreach ($ranges as $key => $range) {
            $this->assertSame($key, (string)$range);
        }

        shuffle($rangeStrings);

        $ranges = IntegerRanges::newFromRangeIterable($rangeStrings);

        $this->assertSame($expectedStr, (string)$ranges);
    }

    public function newFromRangeIterableProvider(): array
    {
        return [
            [ [], '' ],
            [ [ '1:2', ':' ], ':' ],
            [ [ ':1', '-3:', '0:' ], ':' ],
            [ [ ':1', ':-4', '1:' ], ':' ],
            [ [ ':1', '2:' ], ':' ],
            [ [ ':-2', ':-3', '4:6' ], ':-2 4:6' ],
            [ [ '1:2', '4:5', '6:', '6:', '8:' ], '1:2 4:' ],
            [ [ ':-5', ':-1', '1:', '7:' ], ':-1 1:' ],
            [ [ ':-1', '0', '1:3', '3:7', '6:' ], ':' ],
            [
                [ '-3:0', '2:3', '4:7', '9:12', '11:12', '11:13' ],
                '-3:0 2:7 9:13'
            ]
        ];
    }
}
