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
class NonNegativeRange implements RangeInterface
{
    use RangeTrait;

    /**
     * @brief Create from string.
     *
     * Supports an empty string or the syntax `<min>[-[<max>]]`.
     */
    public static function newFromString(string $str): self
    {
        /** Ignore surrounding whitespace. */
        $str = trim($str);

        /** The empty string represents the range [0,∞[. */
        if ($str == '') {
            return new static();
        }

        /** @throw alcamo::exception::SyntaxError if the input is
         *  syntactically wrong. */
        if (
            !preg_match(
                '/^(\d+)(\s*-\s*(\d+)?)?$/',
                $str,
                $matches,
                PREG_UNMATCHED_AS_NULL
            )
        ) {
            throw (new SyntaxError())->setMessageContext(
                [
                    'inData' => $str,
                    'extraMessage' => 'not a valid nonnegative range'
                ]
            );
        }

        $min = intval($matches[1]);

        $max = isset($matches[3])
            ? intval($matches[3])
            : (isset($matches[2]) ? null : $min);

        return new static($min, $max);
    }


    /**
     * @param $min Minimum (nonnegative integer)
     *
     * @param $max Maximum (nonnegative integer or null)
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
     * @brief Whether there is an explicit lower or upper bound
     *
     * A lower bound of 0 is not taken into account since this is implied by
     * the underlying data type of nonnegative integer. This implies that
     * __toString() will represent the interval [0,∞[ as an empty string.
     */
    public function isDefined(): bool
    {
        return $this->min_ || isset($this->max_);
    }

    /// Whether $value is contained in the defined range
    public function contains(int $value): bool
    {
        return $this->min_ <= $value
            && (!isset($this->max_) || $value <= $this->max_);
    }
}
