<?php

namespace alcamo\range;

use alcamo\exception\{OutOfRange, SyntaxError};

/**
 * @brief Range of nonnegative integers
 *
 * @invariant Immutable class.
 *
 * @invariant getMin() always returns a nonnegative integer.
 *
 * @date Last reviewed 2025-10-22
 */
class NonNegativeRange extends AbstractRange
{
    public static function newFromString(string $str): RangeInterface
    {
        $a = static::splitString($str);

        if (
            isset($a[0]) && !ctype_digit($a[0])
                || isset($a[1]) && !ctype_digit($a[1])
        ) {
            /** @throw alcamo::exception::SyntaxError if the range limits are
             *  not valid decimal integers. */
            throw (new SyntaxError())->setMessageContext(
                [
                    'inData' => $str,
                    'extraMessage' => 'not a valid nonnegative range'
                ]
            );
        }

        return new static(...$a);
    }

    /**
     * @param $min Minimum (nonnegative integer or `null`).
     *
     * @param $max Maximum (nonnegative integer or `null`).
     */
    public function __construct(?int $min = null, ?int $max = null)
    {
        /** @throw alcamo::exception::OutOfRange if $min is less than zero. */
        if ($min < 0) {
            throw (new OutOfRange())->setMessageContext(
                [
                    'value' => $min,
                    'lowerBound' => 0
                ]
            );
        }

        /** @throw alcamo::exception::OutOfRange if $max is less than $min. */
        if (isset($max) && $max < $min) {
            throw (new OutOfRange())->setMessageContext(
                [
                    'value' => $max,
                    'lowerBound' => $min
                ]
            );
        }

        $this->min_ = (int)$min;
        $this->max_ = $max;
    }

    /**
     * @copydoc alcamo::range::RangeInterface::isDefined()
     *
     * A lower bound of 0 is not taken into account since this is implied by
     * the underlying data type of nonnegative integer. This implies that
     * __toString() will represent the interval [0,∞[ as an empty string.
     */
    public function isDefined(): bool
    {
        return $this->min_ || isset($this->max_);
    }

    public function contains($value): bool
    {
        $value = (int)$value;

        return $this->min_ <= $value
            && (!isset($this->max_) || $value <= $this->max_);
    }
}
