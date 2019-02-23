# PHP IP Library

IPv4/IPv6 manipulation library for PHP inspired by Python [ipaddress](https://docs.python.org/dev/library/ipaddress.html).

[![Build Status](https://travis-ci.org/rlanvin/php-ip.svg?branch=master)](https://travis-ci.org/rlanvin/php-ip)
[![Latest Stable Version](https://poser.pugx.org/rlanvin/php-ip/v/stable)](https://packagist.org/packages/rlanvin/php-ip)
[![Total Downloads](https://poser.pugx.org/rlanvin/php-ip/downloads)](https://packagist.org/packages/rlanvin/php-ip)

## Requirements

- PHP >= 7.0
- IPv6 support enabled
- GMP extension (www.php.net/manual/en/book.gmp.php)

## Installation

The recommended way is to install the lib [through Composer](http://getcomposer.org/).

Just add this to your `composer.json` file (change the version by the release you want, or use `dev-master` for the development version):

```JSON
{
    "require": {
        "rlanvin/php-ip": "1.*"
    }
}
```

Then run `composer install` or `composer update`.

Now you can use the autoloader, and you will have access to the library:

```php
<?php
require 'vendor/autoload.php';
```

## Documentation

Complete doc is available in [the wiki](https://github.com/rlanvin/php-ip/wiki).

## Contribution

Feel free to contribute! Just create a new issue or a new pull request.

## License

This library is released under the MIT License.
