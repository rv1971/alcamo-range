<?php

namespace alcamo\range;

/**
 * @brief Implementation of RangeInterface
 *
 * @date Last reviewed 2025-10-22
 */
trait RangeTrait
{
    protected $min_; ///< Minimum value or `null`
    protected $max_; ///< Maximum value or `null`

    public function __toString(): string
    {
        /** Return empty string if undefined. */
        if (!$this->isDefined()) {
            return '';
        }

        /** Otherwise, return a value if the range is an exact value. */
        if ($this->isExactValue()) {
            return (string)$this->min_;
        }

        /** Otherwise, return `<min>-<max>`. */
        return "{$this->min_}-{$this->max_}";
    }

    /** @copydoc alcamo::range::RangeInterface::getMin() */
    public function getMin()
    {
        return $this->min_;
    }

    /** @copydoc alcamo::range::RangeInterface::getMax() */
    public function getMax()
    {
        return $this->max_;
    }

    /** @copydoc alcamo::range::RangeInterface::isDefined() */
    public function isDefined(): bool
    {
        return isset($this->min_) || isset($this->max_);
    }

    /** @copydoc alcamo::range::RangeInterface::isBounded() */
    public function isBounded(): bool
    {
        return isset($this->min_) && isset($this->max_);
    }

    /** @copydoc alcamo::range::RangeInterface::isExactValue() */
    public function isExactValue(): bool
    {
        return isset($this->min_) && $this->min_ === $this->max_;
    }
}
