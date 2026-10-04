<?php

namespace alcamo\range;

use PHPUnit\Framework\TestCase;

class NumericPrefixRangesTest extends TestCase
{
    /**
     * @dataProvider newFromRangeIterableProvider
     */
    public function testNewFromRangeIterable($rangeStrings, $expectedStr): void
    {
        $ranges = NumericPrefixRanges::newFromRangeIterable($rangeStrings);

        $this->assertSame($expectedStr, (string)$ranges);

        foreach ($ranges as $key => $range) {
            $this->assertSame((string)$key, (string)$range);
        }

        shuffle($rangeStrings);

        $ranges = NumericPrefixRanges::newFromRangeIterable($rangeStrings);

        $this->assertSame($expectedStr, (string)$ranges);
    }

    public function newFromRangeIterableProvider(): array
    {
        return [
            [ [], '' ],
            [ [ '1234-789', '-' ], '0-9' ],
            [ [ '-1234', '1234-789', '789-' ], '0-9' ],
            [
                [ '-1234', '1234-789', '79001', '7902-' ],
                '00-78 79001 7902-9999'
            ],
            [ [ '-1234', '1234-789', '7891-' ], '0-9' ],
            [ [ '-789', '56-81', '1234-' ], '0-9' ],
            [ [ '-1234', '-13', '789-999' ], '00-13 789-999' ]
        ];
    }

    /**
     * @dataProvider newFromValueIterableProvider
     */
    public function testNewFromValueIterable($values, $expectedStr): void
    {
        $ranges = NumericPrefixRanges::newFromValueIterable($values);

        $this->assertSame($expectedStr, (string)$ranges);

        foreach ($ranges as $key => $range) {
            $this->assertSame((string)$key, (string)$range);
        }

        shuffle($values);

        $ranges = NumericPrefixRanges::newFromValueIterable($values);

        $this->assertSame($expectedStr, (string)$ranges);
    }

    public function newFromValueIterableProvider(): array
    {
        return [
            [ [], '' ],
            [ [ '2', '32', '33', '34', '7', '8' ], '2 32-34 7-8' ]
        ];
    }

    /**
     * @dataProvider cropProvider
     */
    public function testCrop($rangeStrings, $maxLength, $expectedStr): void
    {
        $ranges = NumericPrefixRanges::newFromRangeIterable($rangeStrings)
            ->crop($maxLength);

        $this->assertSame($expectedStr, (string)$ranges);

        shuffle($rangeStrings);

        $ranges = NumericPrefixRanges::newFromRangeIterable($rangeStrings)
            ->crop($maxLength);

        $this->assertSame($expectedStr, (string)$ranges);
    }

    public function cropProvider(): array
    {
        return [
            [ [], 3, '' ],
            [ [ '1234-5678', '568', '569', '5702-58' ], 3, '123-589' ]
        ];
    }

    /**
     * @dataProvider toArrayProvider
     */
    public function testToArray($rangeStrings, $expectedArray): void
    {
        $ranges = NumericPrefixRanges::newFromRangeIterable($rangeStrings);

        $this->assertSame($expectedArray, $ranges->toArray());

        shuffle($rangeStrings);

        $ranges = NumericPrefixRanges::newFromRangeIterable($rangeStrings);

        $this->assertSame($expectedArray, $ranges->toArray());
    }

    public function toArrayProvider(): array
    {
        return [
            [ [], [] ],
            [
                [ '1-2', '4', '51', '6-72' ],
                [ '1', '2', '4', '51', '6', '70', '71', '72' ]
            ]
        ];
    }
}
