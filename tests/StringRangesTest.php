<?php

namespace alcamo\range;

use PHPUnit\Framework\TestCase;

class StringRangesTest extends TestCase
{
    /**
     * @dataProvider newFromStringProvider
     */
    public function testNewFromIterable($rangeStrings, $expectedStr): void
    {
        $ranges = StringRanges::newFromIterable($rangeStrings);

        $this->assertSame($expectedStr, (string)$ranges);

        foreach ($ranges as $key => $range) {
            $this->assertSame($key, (string)$range);
        }

        shuffle($rangeStrings);

        $ranges = StringRanges::newFromIterable($rangeStrings);

        $this->assertSame($expectedStr, (string)$ranges);
    }

    public function newFromStringProvider(): array
    {
        return [
            [ [], '' ],
            [ [ 'bar-foo', '-' ], '-' ],
            [ [ '-bar', 'bar-foo', 'foo-' ], '-' ],
            [ [ '-bar', 'bar-foo', 'foox-' ], '-foo foox-' ],
            [ [ '-foo', 'corge-quux', 'bar-' ], '-' ],
            [ [ '-bar', '-baz', 'foo-qux' ], '-baz foo-qux' ],
            [
                [ 'bar-baz', 'baz-corge', 'corge-foo', 'quux-qux', 'qux-' ],
                'bar-foo quux-'
            ]
        ];
    }
}
