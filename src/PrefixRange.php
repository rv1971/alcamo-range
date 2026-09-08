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

    /// Return new object with borders cropped to given maxLength
    public function crop(int $maxLength): self
    {
        return new static(
            substr($this->min_, 0, $maxLength),
            substr($this->max_, 0, $maxLength)
        );
    }
}
