<?php

namespace alcamo\range;

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

        /* The strlen() condition ensures that 'zzz' is *not* considered to
         * touch 'aaaa'. */

        if (isset($this->max_)) {
            $thisMaxPlus = $this->max_;
            $thisMaxPlus++;

            if (
                $range->min_ === $thisMaxPlus
                    && strlen($thisMaxPlus) == strlen($this->max_)
            ) {
                return true;
            }
        }

        if (isset($range->max_)) {
            $rangeMaxPlus = $range->max_;
            $rangeMaxPlus++;

            if (
                $this->min_ === $rangeMaxPlus
                    && strlen($rangeMaxPlus) == strlen($range->max_)
            ) {
                return true;
            }
        }

        return false;
    }

    /// Return new object with borders cropped to given maxLength
    public function crop(int $maxLength): self
    {
        return new static(
            substr($this->min_, 0, $maxLength),
            substr($this->max_, 0, $maxLength)
        );
    }
}
