<?php

declare(strict_types=1);

/**
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 *
 * @see https://github.com/rlanvin/php-ip
 */

namespace PhpIP\Tests;

use PhpIP\IPv4;
use PHPUnit\Framework\TestCase;

class IPv4Test extends TestCase
{
    // see http://www.miniwebtool.com/ip-address-to-binary-converter/
    // and http://www.miniwebtool.com/ip-address-to-hex-converter
    public function validAddresses()
    {
        $values = array(
            //    IP                            String             Decimal       Binary                              Hexadecimal
            array('127.0.0.1',                  '127.0.0.1',       '2130706433', '1111111000000000000000000000001',  '7f000001'),
            array('10.0.0.1',                   '10.0.0.1',        '167772161',  '1010000000000000000000000001',     'a000001'),
            array('0.0.0.0',                    '0.0.0.0',         '0',          '0',                                '0'),
            array('0.0.0.1',                    '0.0.0.1',         '1',          '1',                                '1'),
            array('255.255.255.254',            '255.255.255.254', '4294967294', '11111111111111111111111111111110', 'fffffffe'),
            array('255.255.255.255',            '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'),
            array(ip2long('10.0.0.1'),          '10.0.0.1',        '167772161',  '1010000000000000000000000001',     'a000001'),
            array(ip2long('255.255.255.255'),   '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'),
            array('1',                          '0.0.0.1',         '1',          '1',                                '1'),
            array('4294967295',                 '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'),
            array(inet_pton('10.0.0.1'),        '10.0.0.1',        '167772161',  '1010000000000000000000000001',     'a000001'),
            array(inet_pton('255.255.255.255'), '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'),
        );

        // 32 bits
        if (PHP_INT_SIZE == 4) {
            $values = array_merge($values, array(
                array(-1, '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'),
            ));
        }
        // 64 bits
        elseif (PHP_INT_SIZE == 8) {
        }

        return $values;
    }

    public function invalidAddresses()
    {
        $values = array(
            array("\t"),
            array('abc'),
            array(12.3),
            array(-12.3),
            array('-1'),
            array('4294967296'),
            array('2a01:8200::'),
            array('::1'),
            array((float) -1),
        );

        // 32 bits
        if (PHP_INT_SIZE == 4) {
            $values = array_merge($values, array(
                // 32bits
                array(-4294967295),
            ));
        }
        // 64 bits
        elseif (PHP_INT_SIZE == 8) {
            $values = array_merge($values, array(
                array(-1),
            ));
        }

        return $values;
    }

    /**
     * @dataProvider validAddresses
     */
    public function testConstructValid($ip, $string)
    {
        $instance = new IPv4($ip);
        $this->assertEquals($string, (string) $instance);
    }

    /**
     * @dataProvider invalidAddresses
     */
    public function testConstructInvalid($ip)
    {
        $this->expectException(\InvalidArgumentException::class);

        $instance = new IPv4($ip);
    }

    /**
     * @dataProvider validAddresses
     */
    public function testConvertToNumeric($ip, $string, $dec, $bin, $hex)
    {
        $instance = new IPv4($ip);
        $this->assertEquals($dec, $instance->numeric(), "Base 10 conversion of $string");
        $this->assertEquals($bin, $instance->numeric(2), "Base 2 (bin) conversion of $string");
        $this->assertEquals($hex, $instance->numeric(16), "Base 16 (hex) conversion of $string");
    }

    public function privateAddresses()
    {
        return array(
            array('127.0.0.1'),
            array('192.168.0.1'),
        );
    }

    /**
     * @dataProvider privateAddresses
     */
    public function testIsPrivate($ip)
    {
        $ip = new IPv4($ip);
        $this->assertTrue($ip->isPrivate(), "$ip is private");
        $this->assertFalse($ip->isPublic(), "$ip is not public");
    }

    public function testGetVersion()
    {
        $ipv4 = new IPv4('10.0.0.1');
        $this->assertEquals(4, $ipv4->getVersion());
    }

    public function humanReadableAddresses()
    {
        return array(
            array('0.0.0.0', '000.000.000.000'),
            array('1.1.1.1', '001.001.001.001'),
            array('127.0.0.1', '127.000.000.001'),
            array('10.8.8.8', '010.008.008.008'),
            array('10.20.30.40', '010.020.030.040'),
            array('99.100.100.1', '099.100.100.001'),
            array('255.255.255.255', '255.255.255.255'),
        );
    }

    /**
     * @dataProvider humanReadableAddresses
     *
     * @param $shortForm
     * @param $longForm
     */
    public function testHumanReadable($shortForm, $longForm)
    {
        $ip = new IPv4($shortForm);
        $this->assertEquals($shortForm, $ip->humanReadable(true));
        $this->assertEquals($shortForm, $ip->humanReadable());
        $this->assertEquals($longForm, $ip->humanReadable(false));
    }

    /**
     * @return array
     */
    public function getValidLinkLocalAddresses(): array
    {
        return array(
            array('169.254.0.0'),
            array('169.254.65.180'),
            array('169.254.99.24'),
            array('169.254.122.33'),
            array('169.254.250.163'),
            array('169.254.211.154'),
            array('169.254.23.170'),
            array('169.254.123.129'),
            array('169.254.158.199'),
            array('169.254.252.99'),
            array('169.254.136.202'),
            array('169.254.131.23'),
            array('169.254.153.225'),
            array('169.254.21.109'),
            array('169.254.197.155'),
            array('169.254.255.255'),
        );
    }

    /**
     * @return array
     */
    public function getInvalidLoopbackTestAddresses(): array
    {
        return array(
            array('0.0.0.0'),
            array('1.1.1.1'),
            array('10.8.8.8'),
            array('10.20.30.40'),
            array('99.100.100.1'),
            array('255.255.255.255'),
            array('119.15.96.43'),
        );
    }

    /**
     * @return array
     */
    public function getInvalidLinkLocalAddresses(): array
    {
        return array(
            array('169.255.0.0'),
            array('25.78.116.246'),
            array('139.19.73.252'),
            array('89.183.190.208'),
            array('158.220.39.20'),
            array('59.185.255.194'),
            array('34.229.155.121'),
            array('200.18.62.158'),
            array('125.10.42.112'),
            array('96.214.253.80'),
            array('252.230.210.27'),
            array('159.125.194.188'),
            array('111.19.68.231'),
            array('43.201.45.207'),
            array('128.163.145.183'),
            array('118.223.86.83'),
        );
    }

    /**
     * @return array
     */
    public function getValidLoopbackTestAddresses(): array
    {
        return array(
            array('127.0.0.0'),
            array('127.0.0.1'),
            array('127.10.0.1'),
            array('127.10.99.1'),
            array('127.10.99.87'),
            array('127.255.255.255'),
            array('127.255.255.0'),
        );
    }

    /**
     * @dataProvider getValidLinkLocalAddresses
     *
     * @param string $address
     */
    public function testIsLinkLocalReturnsTrueForValidAddresses(string $address)
    {
        $ip = new IPv4($address);
        $this->assertTrue($ip->isLinkLocal());
    }

    /**
     * @dataProvider getInvalidLinkLocalAddresses
     *
     * @param string $address
     */
    public function testIsLinkLocalReturnsFalseForInvalidAddresses(string $address)
    {
        $ip = new IPv4($address);
        $this->assertFalse($ip->isLinkLocal());
    }

    /**
     * @dataProvider getValidLoopbackTestAddresses
     *
     * @param string $validLoopback
     */
    public function testIsLoopbackIsTrueForValidLoopbackAddresses(string $validLoopback)
    {
        $valid = new IPv4($validLoopback);
        $this->assertTrue($valid->isLoopback());
    }

    /**
     * @dataProvider getInvalidLoopbackTestAddresses
     *
     * @param string $invalidLoopback
     */
    public function testIsLoopbackIsFalseForInvalidLoopbackAddresses(string $invalidLoopback)
    {
        $valid = new IPv4($invalidLoopback);
        $this->assertFalse($valid->isLoopback());
    }
}
