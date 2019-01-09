Changelog for phpIP 2.0
=======================

* Support for PHP <7 removed.
* `IPBlock::getSubblocks()` renamed to `IPBlock::getSubBlocks()` (camel case).
* `IPBlock::getSuper()` renamed to `IPBlock::getSuperBlock()`.
* All classes moved to `phpIP` namespace.
* `IP::numeric()` will no longer prepend leading zeroes on its binary or hexadecimal representations.
