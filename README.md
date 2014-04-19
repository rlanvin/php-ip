# PHP IP Library

IPv4/IPv6 manipulation library for PHP inspired by Python [ipaddress](https://docs.python.org/dev/library/ipaddress.html).

## Requirements

- PHP >= 5.2
- IPv6 support enabled
- GMP extension (www.php.net/manual/en/book.gmp.php)

## Installation

### Option 1

- [Download the single-file version](https://raw.githubusercontent.com/rlanvin/php-ip/master/ip.lib.php)
- `include` or `require` it
- Done

### Option 2

I'm working on it!

## Documentation

Complete doc is available in [the wiki](https://github.com/rlanvin/php-ip/wiki).

## Disclaimer

I built this library for a project running PHP 5.2 (yep that's old). PHP 5.2 doesn't support late static binding, traits, namespaces and the like. So that explains some of the quirks of the design.

## License

This library is released under the MIT License.