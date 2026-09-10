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

    public function createUnionWith(RangeInterface $range): ?RangeInterface
    {
        if (get_class($range) != static::class) {
            return null;
        }

        if (isset($this->min_) && $range->contains($this->min_)) {
            return new static(
                $range->min_,
                isset($this->max_) && isset($range->max_)
                    ? max($this->max_, $range->max_)
                    : null
            );
        }

        if (isset($range->min_) && $this->contains($range->min_)) {
            return new static(
                $this->min_,
                isset($this->max_) && isset($range->max_)
                    ? max($this->max_, $range->max_)
                    : null
            );
        }

        if (!isset($this->min_) && !isset($range->min_)) {
            return new static(
                null,
                isset($this->max_) && isset($range->max_)
                    ? max($this->max_, $range->max_)
                    : null
            );
        }

        return null;
    }
}
