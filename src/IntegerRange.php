<?php

namespace alcamo\range;

use alcamo\exception\{OutOfRange, SyntaxError};

/**
 * @brief Range of integers
 *
 * @invariant Immutable class.
 *
 * @date Last reviewed 2026-09-08
 */
class IntegerRange extends AbstractRange
{
    public const SEPARATOR = ':';

    public static function newFromString(string $str)
    {
        $a = static::splitString($str);

        if (
            isset($a[0]) && !preg_match('/^-?(\d+)$/', $a[0])
                || isset($a[1]) && !preg_match('/^-?(\d+)$/', $a[1])
        ) {
            /** @throw alcamo::exception::SyntaxError if the range limits are
             *  not valid decimal integers. */
            throw (new SyntaxError())->setMessageContext(
                [
                    'inData' => $str,
                    'extraMessage' => 'not a valid integer range'
                ]
            );
        }

        return new static(...$a);
    }

    /**
     * @param $min Minimum (integer or `null`).
     *
     * @param $max Maximum (integer or `null`).
     */
    public function __construct(?int $min = null, ?int $max = null)
    {
        /** @throw alcamo::exception::OutOfRange if $max is less than $min. */
        if (isset($max) && $max < $min) {
            throw (new OutOfRange())->setMessageContext(
                [
                    'value' => $max,
                    'lowerBound' => $min
                ]
            );
        }

        $this->min_ = $min;
        $this->max_ = $max;
    }

    public function contains($value): bool
    {
        $value = (int)$value;

        return (!isset($this->min_) || $this->min_ <= $value)
            && (!isset($this->max_) || $value <= $this->max_);
    }
}
