<?php

namespace alcamo\range;

/**
 * @brief Collection of integer ranges
 *
 * @invariant Immutable class. Items are sorted using the
 * IntegerRange::compare().
 *
 * @date Last reviewed 2026-09-22
 */
class IntegerRanges extends AbstractRanges
{
    public const ITEM_CLASS = IntegerRange::class;
}
