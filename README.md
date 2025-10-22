# Usage example

~~~
use alcamo\range\NonNegativeRange;

$range = NonNegativeRange::newFromString('3-7');

foreach ([-1, 0, 2, 3, 5, 7, 8] as $value) {
    echo "contains $value: " . $range->contains($value) . "\n";
}
~~~

This will output:

~~~
contains -1: 0
contains 0: 0
contains 2: 0
contains 3: 1
contains 5: 1
contains 7: 1
contains 8: 0
~~~

# Provided interfaces, traits and classes

* The interface `RangeInterface` provides a very basic interface for
  ranges of any kind.
* The trait `RangeTrait` provides a simple implementation which covers
  most of what is needed for `RangeInterface`.
* The class `NonNegativeRange` provides an implementation of a range
  of nonnegative integers, potentially unbounded from above, including
  the bound(s).
