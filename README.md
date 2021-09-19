# PHP IP Library

IPv4/IPv6 manipulation library for PHP inspired by Python [ipaddress](https://docs.python.org/dev/library/ipaddress.html).

[![Build status](https://github.com/rlanvin/php-ip/workflows/Tests/badge.svg)](https://github.com/rlanvin/php-ip/actions)
[![Latest Stable Version](https://poser.pugx.org/rlanvin/php-ip/v/stable)](https://packagist.org/packages/rlanvin/php-ip)
[![Total Downloads](https://poser.pugx.org/rlanvin/php-ip/downloads)](https://packagist.org/packages/rlanvin/php-ip)

## Requirements

- PHP >= 7.0
- IPv6 support enabled
- GMP extension (www.php.net/manual/en/book.gmp.php)

## Installation

The recommended way is to install the lib [through Composer](http://getcomposer.org/).

Simply run `composer require rlanvin/php-ip` for it to be automatically installed and included in your `composer.json`.

Now you can use the autoloader, and you will have access to the library:

```php
require 'vendor/autoload.php';
```

## Documentation

Complete doc is available in [the wiki](https://github.com/rlanvin/php-ip/wiki).

## Related projects

There are some Open Source libraries, built by community on top of this this one:

- [hiqdev/php-ip-tools](https://github.com/hiqdev/php-ip-tools) – tooling for IP address calculations, such as free IP blocks calculation and IP ranges parsing.
- Built one? Add it here.

## Contribution

Feel free to contribute! Just create a new issue or a new pull request.

## License

This library is released under the MIT License.
