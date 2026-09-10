<?php

namespace alcamo\range;

use PHPUnit\Framework\TestCase;

class PrefixRangeTest extends TestCase
{
    /**
     * @dataProvider getCommonPrefixProvider
     */
    public function testGetCommonPrefix($min, $max, $expectedPrefix): void
    {
        $range = new PrefixRange($min, $max);

        $this->assertSame($expectedPrefix, $range->getCommonPrefix());
    }

    public function getCommonPrefixProvider(): array
    {
        return [
            [ null, null, '' ],
            [ null, 'foo', '' ],
            [ 'bar', 'foo', '' ],
            [ 'bar', 'baz', 'ba' ],
            [ 'baar', 'baazz', 'baa' ]
        ];
    }

    /**
     * @dataProvider containsProvider
     */
    public function testContains($range, $value, $expectedResult): void
    {
        $this->assertSame(
            $expectedResult,
            PrefixRange::newFromString($range)->contains($value)
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
            'right-3' => [ '-foo', 'fooo', true ],
            'both-1'  => [ 'bar-foo', 'ba', false ],
            'both-2'  => [ 'bar-foo', 'bar', true ],
            'both-3'  => [ 'bar-foo', 'foo', true ],
            'both-4'  => [ 'bar-foo', 'fooo', true ],
            'both-5'  => [ 'bar-foo', 'fop', false ]
        ];
    }

    public function testClassesDisjoint(): void
    {
        $this->assertFalse(
            (new PrefixRange('a', 'c'))->intersects(new StringRange('b', 'd'))
        );

        $this->assertFalse(
            (new PrefixRange('a', 'b'))->touches(new StringRange('c', 'd'))
        );

        $this->assertNull(
            (new PrefixRange('a', 'b'))
                ->createUnionWith(new StringRange('a', 'b'))
        );
    }

    /**
     * @dataProvider touchesProvider
     */
    public function testTouches($range1, $range2, $expectedResult): void
    {
        $this->assertSame(
            $expectedResult,
            PrefixRange::newFromString($range1)
                ->touches(PrefixRange::newFromString($range2))
        );

        $this->assertSame(
            $expectedResult,
            PrefixRange::newFromString($range2)
                ->touches(PrefixRange::newFromString($range1))
        );
    }

    public function touchesProvider(): array
    {
        return [
            [ '', '', false ],
            [ '', 'bar-foo', false ],
            [ '-bar', 'foo-', false ],
            [ '-foo', 'fop-', true ],
            [ '-foo', 'fooo-', false ],
            [ 'bar-bazx', 'bazy-qux', true ],
            [ '00', '01-03', true ],
            [ '-011', '012-', true ]
        ];
    }

    /**
     * @dataProvider createUnionWithProvider
     */
    public function testCreateUnionWith($range1, $range2, $expectedUnion): void
    {
        if (!isset($expectedUnion)) {
            $this->assertNull(
                PrefixRange::newFromString($range1)
                    ->createUnionWith(PrefixRange::newFromString($range2))
            );

            $this->assertNull(
                PrefixRange::newFromString($range2)
                    ->createUnionWith(PrefixRange::newFromString($range1))
            );
        } else {
            $this->assertEquals(
                PrefixRange::newFromString($expectedUnion),
                PrefixRange::newFromString($range1)
                    ->createUnionWith(PrefixRange::newFromString($range2))
            );

            $this->assertEquals(
                PrefixRange::newFromString($expectedUnion),
                PrefixRange::newFromString($range2)
                    ->createUnionWith(PrefixRange::newFromString($range1))
            );
        }
    }

    public function createUnionWithProvider(): array
    {
        return [
            [ '', '', '' ],
            [ '', 'foo-', '' ],
            [ '', '-bar', '' ],
            [ '', 'bar-foo', '' ],
            [ '', 'baz', '' ],
            [ 'bar-', '-foo', '' ],
            [ 'bar-', 'foo-', 'bar-' ],
            [ 'bar-', 'foo-quux', 'bar-' ],
            [ 'bar-', 'foo', 'bar-' ],
            [ 'bar-', 'a', null ],
            [ 'foo-', '-quux', '' ],
            [ 'foo-', '-bar', null ],
            [ 'quux-', 'bar-foo', null ],
            [ '-foo', '-bar', '-foo' ],
            [ '-foo', 'a-bar', '-foo' ],
            [ '-foo', 'quux-qux', null ],
            [ '-foo', 'bar', '-foo' ],
            [ '-foo', 'quux', null ],
            [ 'bar-quux', 'foo-qux', 'bar-qux' ],
            [ 'bar-quux', 'foo', 'bar-quux' ],
            [ 'foo-quux', 'a-bar', null ],
            [ 'foo-quux', 'foo', 'foo-quux' ],
            [ 'foo-quux', 'bar', null ],
            [ 'foo-quux', 'qux', null ],
            [ 'foo-quux', 'quuy-quxyz', 'foo-quxyz' ],
            [ '-foo', 'fop-', '' ],
            [ '-foo', 'fopa-', null ],
            [ 'bar-bazx', 'bazy-qux', 'bar-qux' ]
        ];
    }

    /**
     * @dataProvider cropProvider
     */
    public function testCrop($range, $maxLength, $expectedRange): void
    {
        $this->assertEquals(
            PrefixRange::newFromString($expectedRange),
            PrefixRange::newFromString($range)->crop($maxLength)
        );
    }

    public function cropProvider(): array
    {
        return [
            [ '', 7, '' ],
            [ 'bar-quux', 4, 'bar-quux' ],
            [ 'bar-quux', 3, 'bar-quu' ],
            [ 'bar-quux', 1, 'b-q' ]
        ];
    }
}
