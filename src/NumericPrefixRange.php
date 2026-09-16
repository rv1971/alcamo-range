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

    /// Return matching prefix, or null if no match.
    public function getMatch(string $text): ?string
    {
        $prefix = substr($text, 0, strlen($this->min_));

        return ($this->min_ <= $prefix && $prefix <= $this->max_)
            ? $prefix
            : null;
    }

    /// Create a minimal representation as a list of prefixes
    public function toArray(): array
    {
        if ($this->isExactValue()) {
            return [ $this->min_ ];
        }

        $result = [];

        /*
         * For the following documentation, the bounds are subdivided as
         * follows:
         * - min = common-prefix major-min-digit other-min-digits
         * - max = common-prefix major-max-digit other-max-digits
         */

        $min = $this->min_;
        $max = $this->max_;

        $commonPrefixLength = strlen($this->getCommonPrefix());

        /* If other-min-digits are not made of zeros, create values in [min,
         * common-prefix major-min-digit[. Start at position of last non-zero
         * digit in $min */
        $pos = strlen(rtrim($min, '0')) - 1;

        if ($pos >= 0) {
            for (; $pos > $commonPrefixLength; $pos--) {
                $prefix = substr($min, 0, $pos);

                if ($min[$pos] != 'A') {
                    for ($j = $min[$pos]; $j <= 9; $j++) {
                        $result[] = "$prefix$j";
                    }
                }

                $min[$pos - 1] = $min[$pos - 1] < 9 ? $min[$pos - 1] + 1 : 'A';
            }
        } else {
            $pos = $commonPrefixLength;
        }

        $prefix = $this->getCommonPrefix();

        /* position of last non-9 digit in $max */
        $lastMaxDigitPos = strlen(rtrim($max, '9')) - 1;

        if ($lastMaxDigitPos <= $commonPrefixLength) {
            /* Special case other-max-digits consists in
             * '9's: create values in [common-prefix major-min-digit,
             * common-prefix major-max-digit] */
            for ($j = $min[$pos]; $j <= $max[$pos]; $j++) {
                $result[] = "$prefix$j";
            }
        } else {
            /* Create values in [common-prefix major-min-digit,
             * common-prefix major-max-digit[. */
            for ($j = $min[$pos]; $j < $max[$pos]; $j++) {
                $result[] = "$prefix$j";
            }

            /* Create values in [common-prefix major-max-digit,
             * common-prefix major-max-digit other-max-digits[. */
            for ($pos++; $pos < $lastMaxDigitPos; $pos++) {
                $prefix = substr($max, 0, $pos);

                for ($j = 0; $j < $max[$pos]; $j++) {
                    $result[] = "$prefix$j";
                }
            }

            if ($pos < strlen($max)) {
                /* Create values in [common-prefix major-max-digit
                 * other-max-digits, common-prefix major-max-digit
                 * other-max-digits]. */
                $prefix = substr($max, 0, $pos);

                for ($j = 0; $j <= $max[$pos]; $j++) {
                    $result[] = "$prefix$j";
                }
            }
        }

        return $result;
    }
}
