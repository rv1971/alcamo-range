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

    /// Create a minimal representation as a list of prefixes
    public function toArray(): array
    {
        $result = [];

        foreach ($this as $range) {
            $range->toArray($result);
        }

        return $result;
    }
}
