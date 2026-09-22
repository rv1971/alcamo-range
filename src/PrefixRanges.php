<?php

namespace alcamo\range;

/**
 * @brief Collection of prefix string ranges
 *
 * @invariant Immutable class. Items are sorted using the
 * PrefixRange::compare().
 *
 * @date Last reviewed 2026-09-22
 */
class PrefixRanges extends AbstractRanges
{
    public const ITEM_CLASS = PrefixRange::class;
}
