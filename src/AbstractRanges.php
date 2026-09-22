<?php

namespace alcamo\range;

use alcamo\collection\ReadonlyCollection;

/**
 * @brief Collection of ranges
 *
 * @invariant Immutable class. Items are of type ITEM_CLASS, indexed by their
 * string representation, and sorted using the compare() method in that class.
 *
 * @date Last reviewed 2026-09-22
 */
abstract class AbstractRanges extends ReadonlyCollection
{
    /// Class of the items in the collection
    public const ITEM_CLASS = null;

    public function newFromIterable(iterable $ranges)
    {
        $ranges2 = [];

        $class = static::ITEM_CLASS;

        foreach ($ranges as $range) {
            $ranges2[] = $range instanceof $class
                ? $range
                : $class::newFromString($range);
        }

        return new static($ranges2);
    }

    /**
     * @param $ranges array of objects of class ITEM_CLASS. The class of the
     * items is not checked in this method.
     */
    protected function __construct(array $ranges)
    {
        parent::__construct($this->normalize($ranges));
    }

    public function __toString(): string
    {
        return implode(' ', array_keys($this->data_));
    }

    /// Sort the ranges and unite ranges where possible
    protected function normalize(array $ranges): array
    {
        $class = static::ITEM_CLASS;

        /* Maximum upper bound of ranges unbounded from below. */
        $maxUpperBound = null;

        /* Minimum upper bound of ranges unbounded from above. */
        $minLowerBound = null;

        $ranges2 = [];

        foreach ($ranges as $range) {
            /* If there is a range containing the complete underlying space,
             * return it as the only member. */
            if (!$range->isDefined()) {
                return [ (string)$range => $range ];
            }

            [ $min, $max ] = $range->getMinMax();

            /* Unbound ranges are removed from the list, just keeping track of
             * the largest one. */
            if (!isset($min)) {
                /* $max is know to be set, since $range is defined. */
                if (!isset($maxUpperBound) || $max > $maxUpperBound) {
                    $maxUpperBound = $max;
                }
            } elseif (!isset($max)) {
                /* $min is know to be set, since $range is defined. */
                if (!isset($minLowerBound) || $min < $minLowerBound) {
                    $minLowerBound = $min;
                }
            } else {
                $ranges2[] = $range;
            }
        }

        /* If the maximum upper bound of those ranges which are unbounded from
         * below is greater or equal to the minimum lower bound of those
         * ranges which are unbounded from above, return the entire underlying
         * space as the only range. */
        if (
            isset($maxUpperBound)
                && isset($minLowerBound)
                && $maxUpperBound >= $minLowerBound
        ) {
            $range = new $class(null, null);

            return [ (string)$range => $range ];
        }

        usort($ranges2, [ static::ITEM_CLASS, 'compare' ]);

        /* Prepend the range unbounded from below, if any. */
        if (isset($maxUpperBound)) {
            array_unshift($ranges2, new $class(null, $maxUpperBound));
        }

        /* Append the range unbounded from above, if any. */
        if (isset($minLowerBound)) {
            $ranges2[] = new $class($minLowerBound, null);
        }

        $result = [];

        /* Unite ranges as much as possible. */
        $currentRange = array_shift($ranges2);

        foreach ($ranges2 as $range) {
            $union = $currentRange->createUnionWith($range);

            if (isset($union)) {
                $currentRange = $union;
            } else {
                $result[(string)$currentRange] = $currentRange;
                $currentRange = $range;
            }
        }

        $result[(string)$currentRange] = $currentRange;

        return $result;
    }
}
