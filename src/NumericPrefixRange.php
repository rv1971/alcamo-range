<?php

namespace alcamo\range;

use alcamo\exception\SyntaxError;

/**
 * @brief Range of numeric prefix strings
 *
 * @invariant Immutable class.
 *
 * @invariant getMin() and getMax() always return nonempty strings of the same
 * length.
 *
 * @date Last reviewed 2026-09-10
 */
class NumericPrefixRange extends PrefixRange
{
    public function __construct(?string $min = null, ?string $max = null)
    {
        /** Normalize the range in the sense that redundant trailing '0's are
         *  removed from the lower bound, and trailing '9's are removed from
         *  the upper bound, unless the bounds are equal. */

        if ($min != $max) {
            $min = rtrim((string)$min, '0');
            $max = rtrim((string)$max, '9');
        }

        /** Change an empty lower bound to '0' and an empty upper bound to
         *  '9'. */

        /* This includes all cases where $min and $max are null or the empty
         * string. */
        if ($min == '') {
            $min = '0';
        }

        if ($max == '') {
            $max = '9';
        }

        if (!ctype_digit($min) || !ctype_digit($max)) {
            /** @throw alcamo::exception::SyntaxError if the range limits are
             *  not valid decimal integers. */
            throw (new SyntaxError())->setMessageContext(
                [
                    'inData' => "$min-$max",
                    'extraMessage' => 'not a valid numeric prefix range'
                ]
            );
        }

        if ($min !== $max) {
            $minLen = strlen($min);
            $maxLen = strlen($max);

            /** Pad the lower bound with '0' or the upper bound with '9' as
             *  needed to get the same length. */
            if ($minLen < $maxLen) {
                $min = str_pad($min, $maxLen, '0');
            } elseif ($minLen > $maxLen) {
                $max = str_pad($max, $minLen, '9');
            }
        }

        parent::__construct($min, $max);
    }

    public function touches(RangeInterface $range): bool
    {
        if (get_class($range) != static::class) {
            return false;
        }

        $len1 = strlen($this->min_);
        $len2 = strlen($range->min_);

        if($len1 < $len2) {
            return $this->touches2(
                str_pad($this->min_, $len2, '0'),
                str_pad($this->max_, $len2, '9'),
                $range->min_,
                $range->max_
            );
        } elseif($len1 > $len2) {
            return $this->touches2(
                $this->min_,
                $this->max_,
                str_pad($range->min_, $len1, '0'),
                str_pad($range->max_, $len1, '9')
            );
        } else {
            return $this->touches2(
                $this->min_,
                $this->max_,
                $range->min_,
                $range->max_
            );
        }
    }

    public function createUnionWith(RangeInterface $range): ?RangeInterface
    {
        if (get_class($range) != static::class) {
            return null;
        }

        $len1 = strlen($this->min_);
        $len2 = strlen($range->min_);

        if($len1 < $len2) {
            return $this->createUnionWith2(
                str_pad($this->min_, $len2, '0'),
                str_pad($this->max_, $len2, '9'),
                $range->min_,
                $range->max_
            );
        } elseif($len1 > $len2) {
            return $this->createUnionWith2(
                $this->min_,
                $this->max_,
                str_pad($range->min_, $len1, '0'),
                str_pad($range->max_, $len1, '9')
            );
        } else {
            return $this->createUnionWith2(
                $this->min_,
                $this->max_,
                $range->min_,
                $range->max_
            );
        }
    }
}
