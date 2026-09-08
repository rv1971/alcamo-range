<?php

namespace alcamo\range;

use alcamo\exception\OutOfRange;

/**
 * @brief Range of strings
 *
 * @invariant Immutable class.
 *
 * @invariant getMin() always returns a string, which may be empty.
 *
 * @date Last reviewed 2026-09-08
 */
class StringRange implements RangeInterface
{
    use RangeTrait;

    /**
     * @brief Create range from string
     *
     * - "" and "-" represent the range of all strings.
     * - "x" represents the range ["x", "x"].
     * - "x-y" represents the range ["x", "y"].
     * - "-x" represents the range ["", "x"].
     * - "x-" represents the range of strings greater or equal to "x".
     */
    public static function newFromString(string $str)
    {
        if ($str == '') {
            return new static();
        }

        $a = explode('-', $str);

        if (count($a) == 1) {
            return new static($a[0], $a[0]);
        }

        if ($a[1] == '') {
            $a[1] = null;
        }

        return new static(...$a);
    }

    public function __construct(?string $min = null, ?string $max = null)
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

        $this->min_ = (string)$min;
        $this->max_ = $max;
    }

    public function isDefined(): bool
    {
        return $this->min_ != '' || isset($this->max_);
    }

    /// Whether $value is contained in the defined range
    public function contains(string $value): bool
    {
        return $this->min_ <= $value
            && (!isset($this->max_) || $value <= $this->max_);
    }
}
