<?php

namespace alcamo\range;

/**
 * @brief Range of prefix strings
 *
 * @invariant Immutable class.
 *
 * @invariant getMin() always returns a string, which may be empty.
 *
 * @warning The notion of touching underlying touches() and createUnionWith()
 * relies on PHP's ++ operator for strings. It works correctly within the
 * space of numeric prefixes and within the space of alphabetic prefixes, in
 * the sense that, for instance, '02' comes after '01' and 'abd' comes after
 * 'abc' (preserving case, i.e. 'xzA' comes after 'xyZ'). But there is nothing
 * that comes after '999' or after 'zz'.
 *
 * @date Last reviewed 2026-09-10
 */
class PrefixRange extends StringRange
{
    private $commonPrefix_; ///< Longest common prefix

    public function __construct(?string $min = null, ?string $max = null)
    {
        parent::__construct($min, $max);

        if (isset($this->max_)) {
            if ($this->min_ == $this->max_) {
                $this->commonPrefix_ = $this->min_;
            } else {
                for (
                    $i = 0;
                    isset($this->min_[$i]) && isset($this->max_[$i])
                        && $this->min_[$i] == $this->max_[$i];
                    $i++
                );

                $this->commonPrefix_ = substr($this->min_, 0, $i);
            }
        } else {
            $this->commonPrefix_ = '';
        }
    }

    public function getCommonPrefix(): string
    {
        return $this->commonPrefix_;
    }

    /**
     * @copybrief alcamo::range::RangeInterface::contains()
     *
     * The logic differs from alcamo::range::StringRange::contains() because
     * for the latter, the range [ "bar", "foo" ] does not contain "foox",
     * while in the present implementation it does.
     */
    public function contains($value): bool
    {
        $value = (string)$value;

        return $this->min_ <= $value && (
            !isset($this->max_)
                || $value <= $this->max_
                || substr($value, 0, strlen($this->max_)) == $this->max_
        );
    }

    public function touches(RangeInterface $range): bool
    {
        if (get_class($range) != static::class) {
            return false;
        }

        return $this->touches2(
            $this->min_,
            $this->max_,
            $range->min_,
            $range->max_
        );
    }

    /** @copydoc alcamo::range::RangeInterface::createUnion() */
    public function createUnionWith(RangeInterface $range): ?RangeInterface
    {
        if (get_class($range) != static::class) {
            return null;
        }

        return $this->createUnionWith2(
            $this->min_,
            $this->max_,
            $range->min_,
            $range->max_
        );
    }

    /// Return new object with borders cropped to given maxLength
    public function crop(int $maxLength): self
    {
        return new static(
            substr($this->min_, 0, $maxLength),
            substr($this->max_, 0, $maxLength)
        );
    }

    protected function inc(?string $value)
    {
        switch (true) {
            case $value == '':
                return null;

            case trim($value, '9') == '':
                return ++$value;

            case ctype_digit($value):
                $value = "x$value";
                $value++;
                return substr($value, 1);

            default:
                return ++$value;
        }
    }

    protected function touches2(
        ?string $min1,
        ?string $max1,
        ?string $min2,
        ?string $max2
    ): bool {
        if (isset($max1)) {
            $max1Plus = $this->inc($max1);

            if ($min2 === $max1Plus && strlen($max1Plus) == strlen($max1)) {
                return true;
            }
        }

        if (isset($max2)) {
            $max2Plus = $this->inc($max2);

            if ($min1 === $max2Plus && strlen($max2Plus) == strlen($max2)) {
                return true;
            }
        }

        return false;
    }

    protected function createUnionWith2(
        ?string $min1,
        ?string $max1,
        ?string $min2,
        ?string $max2
    ): ?RangeInterface {
        $max2Plus = $this->inc($max2);

        if (
            $min1 != ''
                && $min2 <= $min1
                && (!isset($max2) || $min1 <= $max2 || $min1 === $max2Plus)
        ) {
            return new static(
                $min2,
                isset($max1) && isset($max2) ? max($max1, $max2) : null
            );
        }

        $max1Plus = $this->inc($max1);

        if (
            $min2 != ''
                && $min1 <= $min2
                && (!isset($max1) || $min2 <= $max1 || $min2 === $max1Plus)
        ) {
            return new static(
                $min1,
                isset($max1) && isset($max2) ? max($max1, $max2) : null
            );
        }

        if ($min1 == '' && $min2 == '') {
            return new static(
                '',
                isset($max1) && isset($max2) ? max($max1, $max2) : null
            );
        }

        return null;
    }
}
