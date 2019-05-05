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

use PhpIP\IP;
use PhpIP\IPv4;
use PHPUnit\Framework\TestCase;

class IPTest extends TestCase
{
    public function validAddresses()
    {
        return [
            ['127.0.0.1', '127.0.0.1', 4],
            ['4294967296', '::1:0:0', 6],
            ['2a01:8200::', '2a01:8200::', 6],
            ['::1', '::1', 6],
            [inet_pton('::1'), '::1', 6],
            [inet_pton('127.0.0.1'), '127.0.0.1', 4],
        ];
    }

    /**
     * @dataProvider validAddresses
     */
    public function testConstructValid($ip, $string, $version)
    {
        $instance = IP::create($ip);
        $this->assertEquals($string, (string) $instance);
        $this->assertEquals($version, $instance->getVersion());
    }

    /**
     * @dataProvider validAddresses
     */
    public function testBinary($ip, $string)
    {
        $instance = IP::create($ip);
        $this->assertEquals(inet_pton($string), $instance->binary());
    }

    public function invalidAddresses()
    {
        return [
            ["\t"],
            ['abc'],
            [12.3],
            [-12.3],
            ['-1'],
        ];
    }

    /**
     * @dataProvider invalidAddresses
     */
    public function testConstructInvalid($ip)
    {
        $this->expectException(\InvalidArgumentException::class);

        $instance = IP::create($ip);
    }

    public function validOperations()
    {
        return [
            //IP                plus              minus             result
            ['255.255.255.255', null,             1,                '255.255.255.254'],
            ['255.255.255.255', -1,               null,             '255.255.255.254'],
            ['0.0.0.0',        '255.255.255.255', null,             '255.255.255.255'],
            ['255.255.255.255', null,            '255.255.255.255', '0.0.0.0'],
            ['0.0.0.0',         1,                null,             '0.0.0.1'],
            ['0.0.0.0',         null,              -1,              '0.0.0.1'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', null, 1, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', -1, null, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe'],
            ['::', 1, null, '::1'],
            ['::', null, -1, '::1'],
        ];
    }

    /**
     * @dataProvider validOperations
     */
    public function testPlusMinus($ip, $plus, $minus, $result)
    {
        $ip = IP::create($ip);
        if ($plus !== null) {
            $this->assertEquals($result, (string) $ip->plus($plus), "$ip + $plus = $result");
            $this->assertEquals((string) $ip, (string) IP::create($result)->minus($plus), "$result - $plus = $ip");
        } elseif ($minus !== null) {
            $this->assertEquals($result, (string) $ip->minus($minus), "$ip - $minus = $result");
            $this->assertEquals((string) $ip, (string) IP::create($result)->plus($minus), "$result + $minus = $ip");
        }
    }

    public function invalidOperations()
    {
        return [
            // IP   plus   minus
            ['255.255.255.255', 1, null],
            ['255.255.255.254', 2, null],
            ['255.255.255.255', null, -1],
            ['255.255.255.254', null, -2],
            ['255.255.255.255', '255.255.255.255', null],
            ['255.255.255.255', IPv4::MAX_INT, null],
            ['0.0.0.0', -1, null],
            ['0.0.0.1', -2, null],
            ['0.0.0.0', null, 1],
            ['0.0.0.1', null, 2],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 1, null],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe', 2, null],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', null, -1],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe', null, -2],
            ['::', -1, null],
            ['::1', -2, null],
            ['::', null, 1],
            ['::1', null, 2],
        ];
    }

    /**
     * @dataProvider invalidOperations
     */
    public function testPlusMinusOob($ip, $plus, $minus)
    {
        $this->expectException(\OutOfBoundsException::class);

        $ip = IP::create($ip);
        if ($plus !== null) {
            $ip->plus($plus);
        } elseif ($minus !== null) {
            $ip->minus($minus);
        }
    }
}
