<?php

namespace alcamo\range;

/**
 * @brief Collection of numeric prefix string ranges
 *
 * @invariant Immutable class. Items are sorted using the
 * NumericPrefixRange::compare().
 *
 * @date Last reviewed 2026-09-22
 */
class NumericPrefixRanges extends PrefixRanges
{
    public const ITEM_CLASS = NumericPrefixRange::class;
}
