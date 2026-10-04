<?php

namespace alcamo\range;

use PHPUnit\Framework\TestCase;

class PrefixRangesTest extends TestCase
{
    /**
     * @dataProvider newFromRangeIterableProvider
     */
    public function testNewFromRangeIterable($rangeStrings, $expectedStr): void
    {
        $ranges = PrefixRanges::newFromRangeIterable($rangeStrings);

        $this->assertSame($expectedStr, (string)$ranges);

        foreach ($ranges as $key => $range) {
            $this->assertSame($key, (string)$range);
        }

        shuffle($rangeStrings);

        $ranges = PrefixRanges::newFromRangeIterable($rangeStrings);

        $this->assertSame($expectedStr, (string)$ranges);
    }

    public function newFromRangeIterableProvider(): array
    {
        return [
            [ [], '' ],
            [ [ 'bar-foo', '-' ], '-' ],
            [ [ '-bar', 'bar-foo', 'foo-' ], '-' ],
            [ [ '-bar', 'bar-foo', 'foox-' ], '-foo foox-' ],
            [ [ '-bar', 'bar-foo', 'fop-' ], '-' ],
            [ [ '-foo', 'corge-quux', 'bar-' ], '-' ],
            [ [ '-bar', '-baz', 'foo-qux' ], '-baz foo-qux' ],
            [
                [ 'bar-baz', 'baz-corge', 'corge-foo', 'quux-qux', 'qux-' ],
                'bar-foo quux-'
            ]
        ];
    }

    /**
     * @dataProvider cropProvider
     */
    public function testCrop($rangeStrings, $maxLength, $expectedStr): void
    {
        $ranges =
            PrefixRanges::newFromRangeIterable($rangeStrings)->crop($maxLength);

        $this->assertSame($expectedStr, (string)$ranges);

        shuffle($rangeStrings);

        $ranges =
            PrefixRanges::newFromRangeIterable($rangeStrings)->crop($maxLength);

        $this->assertSame($expectedStr, (string)$ranges);
    }

    public function cropProvider(): array
    {
        return [
            [ [], 7, '' ],
            [ [ 'bar-foo', '-' ], 7, '-' ],
            [ [ 'bar-foo', '-' ], 2, '-' ],
            [ [ '-bar', 'bar-foo', 'fooxx-' ], 4, '-foo foox-' ],
            [ [ '-bar', 'bar-foo', 'fooxx-' ], 3, '-' ],
            [ [ '-bar', '-baz', 'foo-qux' ], 2, '-ba fo-qu' ],
            [
                [ 'bar-baz', 'c', 'doo-foo', 'h', 'iabcd-kdefg' ],
                1,
                'b-f h-k'
            ]
        ];
    }
}
