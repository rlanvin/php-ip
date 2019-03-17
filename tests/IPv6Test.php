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

use PhpIP\IPv6;
use PHPUnit\Framework\TestCase;

class IPv6Test extends TestCase
{
    public function validAddresses()
    {
        $values = array(
            // IP  compressed  decimal
            array('2a01:8200::', '2a01:8200::', '55835404833073476206743540170770874368'),
            array('2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:db8:85a3::8a2e:370:7334', '42540766452641154071740215577757643572'),
            array('ffff:0db8::', 'ffff:db8::', '340277452873386678732099705461792571392'),
            array('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', '340282366920938463463374607431768211455'),
            array('::1', '::1', '1', '1', '1'),

            // IPv4-mapped IPv6 addresses
            array('0000:0000:0000:0000:0000:0000:127.127.127.127', '::127.127.127.127', '2139062143'),
            array('::ffff:192.0.2.128', '::ffff:192.0.2.128', '281473902969472'),

            // init with numeric representation
            array('332314827956335977770735408709082546176', 'fa01:8200::', '332314827956335977770735408709082546176'),

            // init with GMP ressource
            array(gmp_init('332314827956335977770735408709082546176'), 'fa01:8200::', '332314827956335977770735408709082546176'),
        );

        // 32 bits
        if (PHP_INT_SIZE == 4) {
            $values = array_merge($values, array(
                array(-1, '::255.255.255.255', '4294967295'),
            ));
        }
        // 64 bits
        elseif (PHP_INT_SIZE == 8) {
            $values = array_merge($values, array(
                array(-1, '::ffff:ffff:ffff:ffff', '18446744073709551615'),
            ));
        }

        return $values;
    }

    public function invalidAddresses()
    {
        $values = array(
            array("\t"),
            array(array()),
            array(new \stdClass()),
            array('-1'),
            array(gmp_init('-1')),
            array(gmp_init('340282366920938463463374607431768211456')),
            array('abcz'),
            array(12.3),
            array(-12.3),
            array('127.0.0.1'),
        );

        // 32 bits
        if (PHP_INT_SIZE == 4) {
        }
        // 64 bits
        elseif (PHP_INT_SIZE == 8) {
        }

        return $values;
    }

    /**
     * @dataProvider validAddresses
     */
    public function testConstructValid($ip, $compressed)
    {
        $instance = new IPv6($ip);
        $this->assertEquals($compressed, (string) $instance);
    }

    /**
     * @dataProvider invalidAddresses
     */
    public function testConstructInvalid($ip)
    {
        $this->expectException(\InvalidArgumentException::class);

        $instance = new IPv6($ip);
    }

    /**
     * @dataProvider validAddresses
     */
    public function testConvertToNumeric($ip, $compressed, $dec)
    {
        $instance = new IPv6($ip);
        $array = unpack('H*', inet_pton($compressed));
        $this->assertEquals(ltrim($array[1], 0), $instance->numeric(16), "Base 16 of $compressed");
        $this->assertEquals($dec, $instance->numeric(10), "Base 10 of $compressed");
    }

    public function testGetVersion()
    {
        $ipv6 = new IPv6('2001:acad::8888');
        $this->assertEquals(6, $ipv6->getVersion());
    }

    /**
     * @return array
     */
    public function humanReadableAddresses(): array
    {
        return array(
            array('2a01:8200::', '2a01:8200:0000:0000:0000:0000:0000:0000'),
            array('2001:db8:85a3::8a2e:370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334'),
            array('ffff:db8::', 'ffff:0db8:0000:0000:0000:0000:0000:0000'),
            array('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'),
            array('::1', '0000:0000:0000:0000:0000:0000:0000:0001'),
            array('::', '0000:0000:0000:0000:0000:0000:0000:0000'),
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
        $ip = new IPv6($shortForm);
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
            array('fe80::'),
            array('fe80:2c27:8239:eaea:ac64:bf11:8cbf:7bc3'),
            array('fe80:4ca2:6fa5:1615:b399:3c37:b06:5f20'),
            array('fe80:6163:6af3:a9ce:bc8b:69d7:ce4:2460'),
            array('fe80:35ac:fc8f:933f:3c4c:4644:103c:5f60'),
            array('fe80:f381:1cfc:868c:284d:274d:e963:b688'),
            array('fe80:9eb3:2847:e048:9588:6020:3fbc:d373'),
            array('fe80:48ce:5e67:28ea:a869:aead:1452:a485'),
            array('fe80:2c91:feb0:17db:3127:a8a0:6ed0:62b7'),
            array('fe80:9240:9b2b:52f0:48f9:676a:3d5b:1b35'),
            array('fe80:3410:159b:3947:4d1b:e13f:33af:6e9d'),
            array('fe80:5013:20d7:2222:2b15:dea1:251d:9e09'),
            array('fe80:7099:c8e5:4ab4:cb4e:b592:aee7:782f'),
            array('fe80:7ad8:45f2:2c33:bbf0:892a:122c:e029'),
            array('fe80:fe46:bc9a:d5a1:7c0a:5baa:f087:7a2b'),
            array('febf:ffff:ffff:ffff:ffff:ffff:ffff:ffff'),
        );
    }

    /**
     * @return array
     */
    public function getInvalidLinkLocalAddresses(): array
    {
        return array(
            array('fec0::'),
            array('784:ec5b:3482:4aca:c956:5cd4:1482:79b0'),
            array('e55:3cac:7aeb:dc4c:6a9b:8f4f:b20:31be'),
            array('ecc7:8c14:720b:60f4:4f39:830f:4360:1c8c'),
            array('71e3:a3c:ef5e:520:8024:489e:3138:e14f'),
            array('2635:72dc:cefa:71a7:66bd:26a0:2fcb:d25b'),
            array('caf4:88a1:7237:9ed0:240d:613:6999:5560'),
            array('c662:35a3:7f65:9a45:e6e5:dd3d:cf79:fd9'),
            array('be83:9604:102d:64ff:5ff:4c66:2899:6d37'),
            array('3399:bae2:f27:67ed:e462:7fa2:fc0a:c99c'),
            array('21aa:47d5:f044:4c1f:4d3d:4136:4141:da85'),
            array('7af:3f67:1698:fe2e:7f67:50e7:7152:4048'),
            array('689c:5299:52b4:c61d:716f:a85a:d117:cf18'),
            array('18e0:79d2:6216:99f:9bd6:4b21:406:d468'),
            array('159b:cba7:1366:20c:67:6afd:20b8:5f43'),
            array('fdc6:714e:3c3:5a0a:6c0d:d424:e69d:3f61'),
        );
    }

    /**
     * @dataProvider getValidLinkLocalAddresses
     *
     * @param string $address
     */
    public function testIsLinkLocalReturnsTrueForValidAddresses(string $address)
    {
        $ip = new IPv6($address);
        $this->assertTrue($ip->isLinkLocal());
    }

    /**
     * @dataProvider getInvalidLinkLocalAddresses
     *
     * @param string $address
     */
    public function testIsLinkLocalReturnsFalseForInvalidAddresses(string $address)
    {
        $ip = new IPv6($address);
        $this->assertFalse($ip->isLinkLocal());
    }

    /**
     * @return array
     */
    public function getInvalidLoopbackAddresses(): array
    {
        return array(
            array('2a01:8200::'),
            array('2001:db8:85a3::8a2e:370:7334'),
            array('ffff:db8::'),
            array('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'),
        );
    }

    public function testIsLoopbackReturnsTrue()
    {
        $ip = new IPv6('::1');
        $this->assertTrue($ip->isLoopback());
    }

    /**
     * @dataProvider getInvalidLoopbackAddresses
     *
     * @param string $invalidLoopback
     */
    public function testIsLoopbackReturnsFalseForOtherAddresses(string $invalidLoopback)
    {
        $address = new IPv6($invalidLoopback);

        $this->assertFalse($address->isLoopback());
    }
}
