<?php

namespace alcamo\range;

/**
 * @namespace alcamo::range
 *
 * @brief Ranges of various types
 */

/**
 * @brief Range of values, including lower and upper bound, if defined
 *
 * @date Last reviewed 2025-10-22
 */
interface RangeInterface
{
    public function __toString(): string;

    /// Get lower bound
    public function getMin();

    /// Get upper bound
    public function getMax();

    /// Get 2-element array
    public function getMinMax(): array;

    /**
     * @brief Whether there is any lower or upper bound defined
     *
     * This does not take into account bounds imposed by the underlying data
     * type itself.
     */
    public function isDefined(): bool;

    /// Whether there is a lower and an upper bound
    public function isBounded(): bool;

    /// Whether the range consists of one exact value
    public function isExactValue(): bool;

    /**
     * @brief Whether $value is contained in the defined range
     *
     * Both bounds (if defined) are included in the range.
     */
    public function contains($value): bool;

    /**
     * @brief Whether two ranges have a nonempty intersection
     *
     * @return `true` iff both ranges are objects of the same class and they
     * have a nonempty intersection.
     */
    public function intersects(self $range): bool;

    /**
     * @brief Whether two ranges touch
     *
     * @return `true` iff both ranges are objects of the same class and do not
     * intersect, but their union is again a range. This can be true only for
     * discrete value spaces.
     */
    public function touches(self $range): bool;

    /**
     * @brief Compute the union of both ranges, if it is a range
     *
     * @return New object of the same class iff both ranges are objects of the
     * same class and their union is aganin a range, i.e. either intersects()
     * or touches() is true. Otherwise `null`.
     */
    public function createUnionWith(RangeInterface $range): ?RangeInterface;
}
