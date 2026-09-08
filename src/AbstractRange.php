<?php

namespace alcamo\range;

/**
 * @brief Range
 *
 * @invariant Immutable class.
 *
 * @date Last reviewed 2026-09-08
 */
abstract class AbstractRange implements RangeInterface
{
    use RangeTrait;

    /// Separator in the string representation
    public const SEPARATOR = '-';
}
