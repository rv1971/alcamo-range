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
class StringRange extends AbstractRange
{
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

    /**
     * @copydoc alcamo::range::RangeInterface::isBounded()
     *
     * An empty string as a lower bound is not taken into account since this
     * is implied by the underlying data type.
     */
    public function isDefined(): bool
    {
        return $this->min_ != '' || isset($this->max_);
    }

    public function contains($value): bool
    {
        $value = (string)$value;

        return $this->min_ <= $value
            && (!isset($this->max_) || $value <= $this->max_);
    }

    public function touches(RangeInterface $range): bool
    {
        return false;
    }
}
