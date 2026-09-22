<?php

namespace alcamo\range;

/**
 * @brief Collection of nonnegative integer ranges
 *
 * @invariant Immutable class. Items are sorted using the
 * NonNegativeRange::compare().
 *
 * @date Last reviewed 2026-09-22
 */
class NonNegativeRanges extends AbstractRanges
{
    public const ITEM_CLASS = NonNegativeRange::class;
}
