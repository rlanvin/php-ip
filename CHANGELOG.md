# Changelog

## [Unreleased]

### Backward Compatibility Breaking Changes

- Deprecated auto-detection of binary strings for IPv6 [#57]((https://github.com/rlanvin/php-ip/pull/57)
This means you can't directly pass the result of `inet_pton` to construct an IPv6 instance anymore, because it is
impossible to reliability distinguish some IPv6 human-readable representation from their binary string representation.
Instead, you need to use the explicit factory method `IPv6::createFromBinaryString` if you want to work with `inet_pton`.
Note: this is still supported for IPv4.
- `IPBlock::contains` now throws an `InvalidArgumentException` if mixing IP versions
- `IPBlock::getMask()` renamed to `IPBlock::getNetmask()`
- `IPBlock::getPrefix()` renamed to `IPBlock::getPrefixLength()`
- `IPBlock::getMaxPrefix()` renamed to `IPBlock::getMaxPrefixLength()`
- `IPBlock::getGivenIpWithPrefixLen()` renamed to `IPBlock::getGivenIpWithPrefixLength()`

### Added

- Added explicit factory methods for `IPv4`/`IPv6` classes:
`createFromInt`, `createFromFloat`, `createFromString`, `createFromBinaryString`, `createFromNumericString` and `createFromGmp`

## [2.1.0] - 2020-10-31

### Added

- New method `IP::matches($ip, $mask)` to perform wildcard mask matching common in network Access Control Lists and OSPF dynamic routing [#51](https://github.com/rlanvin/php-ip/pull/51)
- IPv4Block: Allow to specify the prefix also as an old-style netmask [#53](https://github.com/rlanvin/php-ip/pull/53)
- Support for PHP 7.4

## [2.0.0] - 2019-09-01

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

[Unreleased]: https://github.com/rlanvin/php-ip/compare/v2.1.0...HEAD
[2.1.0]: https://github.com/rlanvin/php-ip/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/rlanvin/php-ip/compare/v1.0.1...v2.0.0
[1.0.1]: https://github.com/rlanvin/php-ip/compare/v1.0.0...v1.0.1
