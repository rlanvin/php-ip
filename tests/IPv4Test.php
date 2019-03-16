<?php

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
            array('127.0.0.1',                  '127.0.0.1',       '2130706433', '01111111000000000000000000000001', '7f000001'),
            array('10.0.0.1',                   '10.0.0.1',        '167772161',  '00001010000000000000000000000001', 'a000001'),
            array('0.0.0.0',                    '0.0.0.0',         '0',          '00000000000000000000000000000000', '0'),
            array('0.0.0.1',                    '0.0.0.1',         '1',          '00000000000000000000000000000001', '1'),
            array('255.255.255.254',            '255.255.255.254', '4294967294', '11111111111111111111111111111110', 'fffffffe'),
            array('255.255.255.255',            '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'),
            array(ip2long('10.0.0.1'),          '10.0.0.1',        '167772161',  '00001010000000000000000000000001', 'a000001'),
            array(ip2long('255.255.255.255'),   '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'),
            array('1',                          '0.0.0.1',         '1',          '00000000000000000000000000000001', '1'),
            array('4294967295',                 '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'),
            array(inet_pton('10.0.0.1'),        '10.0.0.1',        '167772161',  '00001010000000000000000000000001', 'a000001'),
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
    public function getValidIpReversePointerPairs(): array
    {
        return array(
            array('212.212.204.85', '85.204.212.212.in-addr.arpa.'),
            array('7.3.184.248', '248.184.3.7.in-addr.arpa.'),
            array('141.163.33.11', '11.33.163.141.in-addr.arpa.'),
            array('118.182.211.120', '120.211.182.118.in-addr.arpa.'),
            array('95.10.94.226', '226.94.10.95.in-addr.arpa.'),
            array('208.243.130.154', '154.130.243.208.in-addr.arpa.'),
            array('19.38.82.106', '106.82.38.19.in-addr.arpa.'),
            array('250.221.154.47', '47.154.221.250.in-addr.arpa.'),
            array('124.167.157.102', '102.157.167.124.in-addr.arpa.'),
            array('3.7.17.69', '69.17.7.3.in-addr.arpa.'),
            array('106.171.27.168', '168.27.171.106.in-addr.arpa.'),
            array('202.149.11.251', '251.11.149.202.in-addr.arpa.'),
            array('78.114.187.209', '209.187.114.78.in-addr.arpa.'),
            array('180.141.22.14', '14.22.141.180.in-addr.arpa.'),
            array('54.62.197.121', '121.197.62.54.in-addr.arpa.'),
            array('133.249.51.204', '204.51.249.133.in-addr.arpa.'),
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
     * @dataProvider getValidIpReversePointerPairs
     *
     * @param string $ip
     * @param string $reversePointer
     */
    public function testGetReversePointer(string $ip, string $reversePointer)
    {
        $ipv4 = new IPv4($ip);
        $this->assertEquals($reversePointer, $ipv4->reversePointer());
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
