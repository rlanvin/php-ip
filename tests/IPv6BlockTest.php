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

use PhpIP\IPv6Block;
use PHPUnit\Framework\TestCase;

class IPv6BlockTest extends TestCase
{
    // see http://www.miniwebtool.com/ip-address-to-binary-converter/
    // and http://www.miniwebtool.com/ip-address-to-hex-converter
    public function validBlocks()
    {
        return [
            //CIDR                                 Mask                             Delta                                 First IP               Last IP
            ['2001:0db8::/30',                    'ffff:fffc::',                   '0:3:ffff:ffff:ffff:ffff:ffff:ffff',  '2001:db8::',          '2001:dbb:ffff:ffff:ffff:ffff:ffff:ffff'],
            ['2001:0db8::/31',                    'ffff:fffe::',                   '0:1:ffff:ffff:ffff:ffff:ffff:ffff',  '2001:db8::',          '2001:db9:ffff:ffff:ffff:ffff:ffff:ffff'],
            ['2001:0db8::/32',                    'ffff:ffff::',                   '::ffff:ffff:ffff:ffff:ffff:ffff',  '2001:db8::',            '2001:db8:ffff:ffff:ffff:ffff:ffff:ffff'],
            ['2001:0db8:85a3::8a2e:0370:7334/64', 'ffff:ffff:ffff:ffff::',         '::ffff:ffff:ffff:ffff',            '2001:db8:85a3::',       '2001:db8:85a3:0:ffff:ffff:ffff:ffff'],
        ];
    }

    /**
     * @dataProvider validBlocks
     */
    public function testConstructValid($block, $mask, $delta, $first_ip, $last_ip)
    {
        $instance = new IPv6Block($block);
        $this->assertEquals($mask, (string) $instance->getMask(), "Mask of $block");
        $this->assertEquals($delta, (string) $instance->getDelta(), "Delta of $block");
        $this->assertEquals($first_ip, (string) $instance->getFirstIp(), "First IP of $block");
        $this->assertEquals($last_ip, (string) $instance->getLastIp(), "Last IP of $block");
    }

    public function invalidBlocks()
    {
        return [
            ['127.0.2666.1/24'],
            ['127.0.0.1/45'],
            ["\t"],
            ['abc'],
            [12.3],
            [-12.3],
            ['-1'],
            ['4294967296'],
            ['2a01:8200::'],
            ['2a01:8200::/'],
            ['::1'],
            ['192.168.0.2/24'],
        ];
    }

    /**
     * @dataProvider invalidBlocks
     */
    public function testConstructInvalid($block)
    {
        $this->expectException(\InvalidArgumentException::class);

        $instance = new IPv6Block($block);
    }

    public function testIterator()
    {
        $expectation = [
            '2001:db8:85a3:a:0:8a2e:370:f0',
            '2001:db8:85a3:a:0:8a2e:370:f1',
            '2001:db8:85a3:a:0:8a2e:370:f2',
            '2001:db8:85a3:a:0:8a2e:370:f3',
            '2001:db8:85a3:a:0:8a2e:370:f4',
            '2001:db8:85a3:a:0:8a2e:370:f5',
            '2001:db8:85a3:a:0:8a2e:370:f6',
            '2001:db8:85a3:a:0:8a2e:370:f7',
            '2001:db8:85a3:a:0:8a2e:370:f8',
            '2001:db8:85a3:a:0:8a2e:370:f9',
            '2001:db8:85a3:a:0:8a2e:370:fa',
            '2001:db8:85a3:a:0:8a2e:370:fb',
            '2001:db8:85a3:a:0:8a2e:370:fc',
            '2001:db8:85a3:a:0:8a2e:370:fd',
            '2001:db8:85a3:a:0:8a2e:370:fe',
            '2001:db8:85a3:a:0:8a2e:370:ff',
        ];

        $subnet = new IPv6Block('2001:0db8:85a3:a:0:8a2e:0370:f0/124');

        $this->assertEquals($expectation, iterator_to_array($subnet->getIterator()));
    }
}
