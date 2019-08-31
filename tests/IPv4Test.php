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
            //IP                            String             Decimal       Binary                              Hexadecimal
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
     * Data provider for testBit_xor().
     *
     * @return array
     */
    public function getXorData(): array
    {
        return [
            //IP_1              IP_2               IP_1 XOR IP_2
            ['102.166.162.196', '147.124.132.249', '245.218.38.61'],
            ['16.52.37.123',    '218.145.148.156', '202.165.177.231'],
            ['71.177.119.225',  '3.54.9.220',      '68.135.126.61'],
            ['7.180.145.95',    '39.79.79.104',    '32.251.222.55'],
            ['82.204.197.236',  '171.80.138.27',   '249.156.79.247'],
            ['243.100.68.134',  '10.217.229.5',    '249.189.161.131'],
            ['211.229.158.56',  '155.177.219.63',  '72.84.69.7'],
            ['118.92.141.120',  '205.98.66.143',   '187.62.207.247'],
            ['134.194.211.62',  '55.115.130.222',  '177.177.81.224'],
            ['27.88.157.167',   '192.120.65.232',  '219.32.220.79'],
        ];
    }

    /**
     * Data provider for testBit_negate().
     *
     * @return array
     */
    public function getNegateData(): array
    {
        return [
            //IP                Negation
            ['255.255.255.255', '0.0.0.0'],
            ['255.255.255.0',   '0.0.0.255'],
            ['255.255.254.0',   '0.0.1.255'],
            ['255.255.252.0',   '0.0.3.255'],
            ['255.255.248.0',   '0.0.7.255'],
            ['255.255.240.0',   '0.0.15.255'],
            ['255.255.224.0',   '0.0.31.255'],
            ['255.255.192.0',   '0.0.63.255'],
            ['255.255.128.0',   '0.0.127.255'],
            ['255.255.0.0',     '0.0.0.255'],
            ['0.0.0.0',         '255.255.255.255'],
            ['128.3.1.1',       '127.252.254.254'],
        ];
    }

    /**
     * Data provider for testMatches().
     *
     * @return array
     */
    public function getMatchesData(): array
    {
        $data = [];

        //Match all addresses within 192.168.0.0/23.
        $data[] = [
            'ip' => '192.168.1.1',
            'mask' => '0.0.1.255',
            'matches' => [
                '192.168.0.0',
                '192.168.0.15',
                '192.168.0.64',
                '192.168.0.255',
                '192.168.1.0',
                '192.168.1.31',
                '192.168.1.192',
                '192.168.1.255',
            ],
            'non_matches' => [
                '192.167.255.255',
                '192.168.2.0',
                '127.0.0.0',
                '0.0.0.0',
                '255.255.255.255',
                '10.0.0.1',
                '200.100.50.0',
                '172.16.1.42',
            ],
        ];

        //Match all addresses in 10.0.0.0/8 where the last octet is an even number.
        $data[] = [
            'ip' => '10.0.0.0',
            'mask' => '0.255.255.254',
            'matches' => [
                '10.0.0.0',
                '10.0.0.2',
                '10.1.10.64',
                '10.3.10.254',
                '10.10.10.4',
                '10.13.13.6',
                '10.64.73.88',
                '10.255.255.254',
            ],
            'non_matches' => [
                '10.0.0.1',
                '10.3.3.3',
                '10.4.4.7',
                '10.10.10.9',
                '10.23.32.41',
                '10.254.254.253',
                '10.255.255.255',
                '192.168.2.2',
            ],
        ];

        //Match any address whose third octet is 2.
        $data[] = [
            'ip' => '123.123.2.13',
            'mask' => IPv4::create('255.255.0.255'),
            'matches' => [
                '0.0.2.0',
                '127.0.2.1',
                '192.168.2.3',
                '2.2.2.2',
                '255.255.2.1',
                '172.16.2.255',
                '224.169.2.15',
                '255.255.2.255',
            ],
            'non_matches' => [
                '2.2.0.2',
                '2.2.22.2',
                '102.166.162.196',
                '16.52.37.123',
                '71.177.119.225',
                '7.180.42.95',
                '82.204.197.236',
                '243.100.68.134',
            ],
        ];

        return $data;
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

    /**
     * @dataProvider getXorData
     *
     * @param string $ip_1
     * @param string $ip_2
     * @param string $xor
     */
    public function testBit_xor(string $ip_1, string $ip_2, string $xor)
    {
        $this->assertEquals(IPv4::create($xor), IPv4::create($ip_1)->bit_xor($ip_2));
    }

    /**
     * @dataProvider getNegateData
     *
     * @param string $ip
     * @param string $negation
     */
    public function testBit_negate(string $ip, string $negation)
    {
        $this->assertEquals(IPv4::create($negation), IPv4::create($ip));
    }

    /**
     * @dataProvider getMatchesData
     *
     * @param mixed $ip
     * @param mixed $mask
     * @param array $matches
     * @param array $non_matches
     */
    public function testMatches($ip, $mask, array $matches, array $non_matches)
    {
        $ip = IPv4::create($ip);

        foreach ($matches as $hostIP) {
            $this->assertTrue($ip->matches($hostIP, $mask), sprintf('Failed asserting host IP "%s" matches with IP: %s and mask %s.', $hostIP, $ip, $mask));
        }

        foreach ($non_matches as $hostIP) {
            $this->assertFalse($ip->matches($hostIP, $mask), sprintf('Failed asserting host IP "%s" DOES NOT match with IP: %s and mask %s.', $hostIP, $ip, $mask));
        }
    }

    /**
     * Test that the IP::matches() mask default of 0 matches the entire IP address exactly.
     */
    public function testDefaultMaskValueMatchesEntireIpAddress()
    {
        $ip = IPv4::create('172.16.3.5');

        $this->assertTrue($ip->matches('172.16.3.5'));
        $this->assertFalse($ip->matches('172.16.3.4'));
    }
}
