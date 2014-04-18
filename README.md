# PHP IP Library

This is a library to manipulate IPv4 and IPv6 addresses with PHP.

## Requirements

PHP >= 5.2
GMP extension (www.php.net/manual/en/book.gmp.php)

## IPv4 and IPv6 base types

By default IP addresses are not easy to manipulate due to INT being unsigned (for IPv4) and the absence of 128 bits integer type (for IPv6).

The library provide classes to abstract IP addresses and make basic calculations with them. Too bad PHP doesn't support operator overloading...

```php
$ip = new IPv4('127.0.0.0');
echo $ip->plus(1),"\n"; // 127.0.0.1
echo $ip->minus(1),"\n"; // 126.255.255.255
echo $ip->bit_or('255.255.0.0'),"\n"; // 255.255.0.0
echo $ip->bit_and('255.255.0.0'),"\n"; // 127.0.0.0

// you can get a numeric representation of the IP as a PHP string
echo $ip->numeric(2),"\n"; // 1111111000000000000000000000000
echo $ip->numeric(10),"\n"; // 2130706432
echo $ip->numeric(16),"\n"; // 7f000000

$ip = new IPv6('2a01:8200::');
echo $ip->plus(1),"\n"; // 2a01:8200::1
echo $ip->minus(1),"\n"; // 2a01:81ff:ffff:ffff:ffff:ffff:ffff:ffff
echo $ip->bit_or('::ffff'),"\n"; // 2a01:8200::ffff
echo $ip->bit_and('ffff::'),"\n"; // 2a01::

// get a numeric representation of the IP as a PHP string
echo $ip->numeric(2),"\n"; // 101010000000011000001000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000"
echo $ip->numeric(10),"\n"; // 55835404833073476206743540170770874368
echo $ip->numeric(16),"\n"; // 2a018200000000000000000000000000

// get the uncompressed representation of IPv6
echo $ip->humanReadable(false),"\n"; // 2a01:8200:0000:0000:0000:0000:0000:0000
```

## Subnets

Subnets are represented as CIDR blocks.

```php
$ipv4block = new IPv4Block('128.0.0.0/16');
echo "Mask = ",$ipv4block->getMask(),"\n"; // 255.255.0.0
echo "Delta = ",$ipv4block->getDelta(),"\n"; // 0.0.255.255
echo "First IP = ",$ipv4block->getFirstIp(),"\n"; // 128.0.0.0
echo "Last IP = ",$ipv4block->getLastIp(),"\n"; // 128.0.255.255

$ipv6block = new IPv6Block('2001:0db8::/32');
echo "Mask = ",$ipv6block->getMask(),"\n"; // ffff:ffff::
echo "Delta = ",$ipv6block->getDelta(),"\n"; // ::ffff:ffff:ffff:ffff:ffff:ffff
echo "First IP = ",$ipv6block->getFirstIp(),"\n"; // 2001:db8::
echo "Last IP = ",$ipv6block->getLastIp(),"\n"; // 2001:db8:ffff:ffff:ffff:ffff:ffff:ffff
```

### Operations on blocks

Works for both IPv4 and IPv6

```php
$block = new IPv4Block('128.0.0.0/24');
$block->contains('128.0.0.42'); // true
$block->contains('128.0.0.0/25'); // true
$block->contains('10.0.0.1'); // false

$ip = new IPv4('128.0.0.0');
$ip->isContainedIn($block); // true
```