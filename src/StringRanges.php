<?php

namespace alcamo\range;

/**
 * @brief Collection of string ranges
 *
 * @invariant Immutable class. Items are sorted using the
 * StringRange::compare().
 *
 * @date Last reviewed 2026-09-22
 */
class StringRanges extends AbstractRanges
{
    public const ITEM_CLASS = StringRange::class;
}
