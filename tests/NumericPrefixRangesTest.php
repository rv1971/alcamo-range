<?php

namespace alcamo\range;

use PHPUnit\Framework\TestCase;

class NumericPrefixRangesTest extends TestCase
{
    /**
     * @dataProvider newFromStringProvider
     */
    public function testNewFromIterable($rangeStrings, $expectedStr): void
    {
        $ranges = NumericPrefixRanges::newFromIterable($rangeStrings);

        $this->assertSame($expectedStr, (string)$ranges);

        foreach ($ranges as $key => $range) {
            $this->assertSame($key, (string)$range);
        }

        shuffle($rangeStrings);

        $ranges = NumericPrefixRanges::newFromIterable($rangeStrings);

        $this->assertSame($expectedStr, (string)$ranges);
    }

    public function newFromStringProvider(): array
    {
        return [
            [ [], '' ],
            [ [ '1234-789', '-' ], '0-9' ],
            [ [ '-1234', '1234-789', '789-' ], '0-9' ],
            [ [ '-1234', '1234-789', '7902-' ], '00-78 7902-9999' ],
            [ [ '-1234', '1234-789', '7891-' ], '0-9' ],
            [ [ '-789', '56-81', '1234-' ], '0-9' ],
            [ [ '-1234', '-13', '789-999' ], '00-13 789-999' ]
        ];
    }
}
