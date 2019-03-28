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
        $values = [
            //IP                           String             Decimal       Binary                              Hexadecimal
            ['127.0.0.1',                  '127.0.0.1',       '2130706433', '01111111000000000000000000000001', '7f000001'],
            ['10.0.0.1',                   '10.0.0.1',        '167772161',  '00001010000000000000000000000001', 'a000001'],
            ['0.0.0.0',                    '0.0.0.0',         '0',          '00000000000000000000000000000000', '0'],
            ['0.0.0.1',                    '0.0.0.1',         '1',          '00000000000000000000000000000001', '1'],
            ['255.255.255.254',            '255.255.255.254', '4294967294', '11111111111111111111111111111110', 'fffffffe'],
            ['255.255.255.255',            '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'],
            [ip2long('10.0.0.1'),          '10.0.0.1',        '167772161',  '00001010000000000000000000000001', 'a000001'],
            [ip2long('255.255.255.255'),   '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'],
            ['1',                          '0.0.0.1',         '1',          '00000000000000000000000000000001', '1'],
            ['4294967295',                 '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'],
            [inet_pton('10.0.0.1'),        '10.0.0.1',        '167772161',  '00001010000000000000000000000001', 'a000001'],
            [inet_pton('255.255.255.255'), '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'],
        ];

        // 32 bits
        if (PHP_INT_SIZE == 4) {
            $values = array_merge($values, [
                [-1, '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'],
            ]);
        }
        // 64 bits
        elseif (PHP_INT_SIZE == 8) {
        }

        return $values;
    }

    public function invalidAddresses()
    {
        $values = [
            ["\t"],
            ['abc'],
            [12.3],
            [-12.3],
            ['-1'],
            ['4294967296'],
            ['2a01:8200::'],
            ['::1'],
            [(float) -1],
        ];

        // 32 bits
        if (PHP_INT_SIZE == 4) {
            $values = array_merge($values, [
                // 32bits
                [-4294967295],
            ]);
        }
        // 64 bits
        elseif (PHP_INT_SIZE == 8) {
            $values = array_merge($values, [
                [-1],
            ]);
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
        return [
            ['127.0.0.1'],
            ['192.168.0.1'],
        ];
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
        return [
            ['0.0.0.0', '000.000.000.000'],
            ['1.1.1.1', '001.001.001.001'],
            ['127.0.0.1', '127.000.000.001'],
            ['10.8.8.8', '010.008.008.008'],
            ['10.20.30.40', '010.020.030.040'],
            ['99.100.100.1', '099.100.100.001'],
            ['255.255.255.255', '255.255.255.255'],
        ];
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
        return [
            ['212.212.204.85', '85.204.212.212.in-addr.arpa.'],
            ['7.3.184.248', '248.184.3.7.in-addr.arpa.'],
            ['141.163.33.11', '11.33.163.141.in-addr.arpa.'],
            ['118.182.211.120', '120.211.182.118.in-addr.arpa.'],
            ['95.10.94.226', '226.94.10.95.in-addr.arpa.'],
            ['208.243.130.154', '154.130.243.208.in-addr.arpa.'],
            ['19.38.82.106', '106.82.38.19.in-addr.arpa.'],
            ['250.221.154.47', '47.154.221.250.in-addr.arpa.'],
            ['124.167.157.102', '102.157.167.124.in-addr.arpa.'],
            ['3.7.17.69', '69.17.7.3.in-addr.arpa.'],
            ['106.171.27.168', '168.27.171.106.in-addr.arpa.'],
            ['202.149.11.251', '251.11.149.202.in-addr.arpa.'],
            ['78.114.187.209', '209.187.114.78.in-addr.arpa.'],
            ['180.141.22.14', '14.22.141.180.in-addr.arpa.'],
            ['54.62.197.121', '121.197.62.54.in-addr.arpa.'],
            ['133.249.51.204', '204.51.249.133.in-addr.arpa.'],
        ];
    }

    /**
     * @return array
     */
    public function getValidLinkLocalAddresses(): array
    {
        return [
            ['169.254.0.0'],
            ['169.254.65.180'],
            ['169.254.99.24'],
            ['169.254.122.33'],
            ['169.254.250.163'],
            ['169.254.211.154'],
            ['169.254.23.170'],
            ['169.254.123.129'],
            ['169.254.158.199'],
            ['169.254.252.99'],
            ['169.254.136.202'],
            ['169.254.131.23'],
            ['169.254.153.225'],
            ['169.254.21.109'],
            ['169.254.197.155'],
            ['169.254.255.255'],
        ];
    }

    /**
     * @return array
     */
    public function getInvalidLoopbackTestAddresses(): array
    {
        return [
            ['0.0.0.0'],
            ['1.1.1.1'],
            ['10.8.8.8'],
            ['10.20.30.40'],
            ['99.100.100.1'],
            ['255.255.255.255'],
            ['119.15.96.43'],
        ];
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
    public function getInvalidLinkLocalAddresses(): array
    {
        return [
            ['169.255.0.0'],
            ['25.78.116.246'],
            ['139.19.73.252'],
            ['89.183.190.208'],
            ['158.220.39.20'],
            ['59.185.255.194'],
            ['34.229.155.121'],
            ['200.18.62.158'],
            ['125.10.42.112'],
            ['96.214.253.80'],
            ['252.230.210.27'],
            ['159.125.194.188'],
            ['111.19.68.231'],
            ['43.201.45.207'],
            ['128.163.145.183'],
            ['118.223.86.83'],
        ];
    }

    /**
     * @return array
     */
    public function getValidLoopbackTestAddresses(): array
    {
        return [
            ['127.0.0.0'],
            ['127.0.0.1'],
            ['127.10.0.1'],
            ['127.10.99.1'],
            ['127.10.99.87'],
            ['127.255.255.255'],
            ['127.255.255.0'],
        ];
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
