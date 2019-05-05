# Changelog

## [Unreleased]

### Backward Compatibility Breaking Changes
- All classes now reside within `PhpIP` namespace. [#14](https://github.com/rlanvin/php-ip/pull/14)
- Deprecated `IPBlock::getSuper`, use `IPBlock::getSuperBlock()` instead. [#46](https://github.com/rlanvin/php-ip/pull/46)

### Added

- New method `isLoopback()` [#37](https://github.com/rlanvin/php-ip/pull/37)
- New method `isLinkLocal()` [#43](https://github.com/rlanvin/php-ip/pull/43)
- New method `reversePointer()` [#44](https://github.com/rlanvin/php-ip/pull/44)
- New method `IpBlock::getGivenIp` to return the IP used in the constructor [#6](https://github.com/rlanvin/php-ip/pull/6)

### Removed 

- Drop support for PHP 5 [#8](https://github.com/rlanvin/php-ip/issues/8)

## [1.0.1] - 2015-06-26

### Fixed

- Compatibility issues with PHP 5.4, 5.5 and 5.6

## 1.0.0 - 2015-04-03

First release

[Unreleased]: https://github.com/rlanvin/php-rrule/compare/v1.0.1...HEAD
[1.0.1]: https://github.com/rlanvin/php-ip/compare/v1.0.0...v1.0.1