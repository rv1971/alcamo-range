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
}
