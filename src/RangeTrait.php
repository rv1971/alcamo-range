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

    /**
     * @brief Create range from string
     *
     * If static::SEPARATOR is '-', then:
     * - "" and "-" represent the range of all strings.
     * - "x" represents the range ["x", "x"].
     * - "x-y" represents the range ["x", "y"].
     * - "-x" represents the range ["", "x"].
     * - "x-" represents the range of strings greater or equal to "x".
     */
    public static function newFromString(string $str)
    {
        return new static(... static::splitString($str));
    }

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

        /** Otherwise, return `<min><separator><max>`. */
        return $this->min_ . static::SEPARATOR . $this->max_;
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

    /** @copydoc alcamo::range::RangeInterface::getMinMax() */
    public function getMinMax(): array
    {
        return [ $this->min_, $this->max_ ];
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

    /// Whether two ranges have a nonempty intersection
    public function intersects(self $range): bool
    {
        return isset($range->min_) && $this->contains($range->min_)
            || isset($this->min_) && $range->contains($this->min_)
            || !isset($range->min_) && !isset($this->min_);
    }

    /// Return a pair suitable as parameters to __construct()
    protected static function splitString(string $str): array
    {
        $str = trim($str);

        if ($str == '') {
            return [ null, null ];
        }

        $a = explode(static::SEPARATOR, $str);

        $a[0] = trim($a[0]);

        if ($a[0] == '') {
            $a[0] = null;
        }

        if (count($a) == 1) {
            return [ $a[0], $a[0] ];
        }

        $a[1] = trim($a[1]);

        if ($a[1] == '') {
            $a[1] = null;
        }

        return [ $a[0], $a[1] ];
    }
}
