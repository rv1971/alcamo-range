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

    public function getMin();

    public function getMax();

    /**
     * @breief Whether there is any lower or upper bound defined
     *
     * This does not include bounds imposed by the data type.
     */
    public function isDefined(): bool;

    /// Whether there a lower and an upper bound
    public function isBounded(): bool;

    /// Whether the range is one exact value
    public function isExactValue(): bool;
}
