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

use PhpIP\IPBlock;
use PhpIP\IPv6Block;
use PhpIP\IPv4Block;
use PhpIP\IP;
use PHPUnit\Framework\TestCase;

class IPBlockTest extends TestCase
{
    /**
     * see http://www.miniwebtool.com/ip-address-to-binary-converter
     * and http://www.miniwebtool.com/ip-address-to-hex-converter
     */
    public function validIPv4Blocks()
    {
        return [
            //CIDR          Mask              Delta             First IP    Last IP            Prefix length
            ['0.0.0.0/0',   '0.0.0.0',        '255.255.255.255','0.0.0.0',  '255.255.255.255','0'],
            ['130.0.0.0/1', '128.0.0.0',      '127.255.255.255','128.0.0.0','255.255.255.255','1'],
            ['130.0.0.0/2', '192.0.0.0',      '63.255.255.255', '128.0.0.0','191.255.255.255','2'],
            ['130.0.0.0/3', '224.0.0.0',      '31.255.255.255', '128.0.0.0','159.255.255.255','3'],
            ['130.0.0.0/4', '240.0.0.0',      '15.255.255.255', '128.0.0.0','143.255.255.255','4'],
            ['130.0.0.0/5', '248.0.0.0',      '7.255.255.255',  '128.0.0.0','135.255.255.255','5'],
            ['130.0.0.0/6', '252.0.0.0',      '3.255.255.255',  '128.0.0.0','131.255.255.255','6'],
            ['128.0.0.0/7', '254.0.0.0',      '1.255.255.255',  '128.0.0.0','129.255.255.255','7'],
            ['127.0.0.0/8', '255.0.0.0',      '0.255.255.255',  '127.0.0.0','127.255.255.255','8'],
            ['127.0.0.0/9', '255.128.0.0',    '0.127.255.255',  '127.0.0.0','127.127.255.255','9'],
            ['127.0.0.0/10','255.192.0.0',    '0.63.255.255',   '127.0.0.0','127.63.255.255', '10'],
            ['127.0.0.0/11','255.224.0.0',    '0.31.255.255',   '127.0.0.0','127.31.255.255', '11'],
            ['127.0.0.0/12','255.240.0.0',    '0.15.255.255',   '127.0.0.0','127.15.255.255', '12'],
            ['127.0.0.0/13','255.248.0.0',    '0.7.255.255',    '127.0.0.0','127.7.255.255',  '13'],
            ['127.0.0.0/14','255.252.0.0',    '0.3.255.255',    '127.0.0.0','127.3.255.255',  '14'],
            ['127.0.0.0/15','255.254.0.0',    '0.1.255.255',    '127.0.0.0','127.1.255.255',  '15'],
            ['127.0.0.0/16','255.255.0.0',    '0.0.255.255',    '127.0.0.0','127.0.255.255',  '16'],
            ['127.0.0.0/17','255.255.128.0',  '0.0.127.255',    '127.0.0.0','127.0.127.255',  '17'],
            ['127.0.0.0/18','255.255.192.0',  '0.0.63.255',     '127.0.0.0','127.0.63.255',   '18'],
            ['127.0.0.0/19','255.255.224.0',  '0.0.31.255',     '127.0.0.0','127.0.31.255',   '19'],
            ['127.0.0.0/20','255.255.240.0',  '0.0.15.255',     '127.0.0.0','127.0.15.255',   '20'],
            ['127.0.0.0/21','255.255.248.0',  '0.0.7.255',      '127.0.0.0','127.0.7.255',    '21'],
            ['127.0.0.0/22','255.255.252.0',  '0.0.3.255',      '127.0.0.0','127.0.3.255',    '22'],
            ['127.0.0.0/23','255.255.254.0',  '0.0.1.255',      '127.0.0.0','127.0.1.255',    '23'],
            ['127.0.0.0/24','255.255.255.0',  '0.0.0.255',      '127.0.0.0','127.0.0.255',    '24'],
            ['127.0.0.0/25','255.255.255.128','0.0.0.127',      '127.0.0.0','127.0.0.127',    '25'],
            ['127.0.0.0/26','255.255.255.192','0.0.0.63',       '127.0.0.0','127.0.0.63',     '26'],
            ['127.0.0.0/27','255.255.255.224','0.0.0.31',       '127.0.0.0','127.0.0.31',     '27'],
            ['127.0.0.0/28','255.255.255.240','0.0.0.15',       '127.0.0.0','127.0.0.15',     '28'],
            ['127.0.0.0/29','255.255.255.248','0.0.0.7',        '127.0.0.0','127.0.0.7',      '29'],
            ['127.0.0.0/30','255.255.255.252','0.0.0.3',        '127.0.0.0','127.0.0.3',      '30'],
            ['127.0.0.0/31','255.255.255.254','0.0.0.1',        '127.0.0.0','127.0.0.1',      '31'],
            ['127.0.0.0/32','255.255.255.255','0.0.0.0',        '127.0.0.0','127.0.0.0',      '32'],

            //Net+Netmask                Mask              Delta             First IP    Last IP
            ['0.0.0.0/0.0.0.0',          '0.0.0.0',        '255.255.255.255','0.0.0.0',  '255.255.255.255','0'],
            ['130.0.0.0/128.0.0.0',      '128.0.0.0',      '127.255.255.255','128.0.0.0','255.255.255.255','1'],
            ['130.0.0.0/192.0.0.0',      '192.0.0.0',      '63.255.255.255', '128.0.0.0','191.255.255.255','2'],
            ['130.0.0.0/224.0.0.0',      '224.0.0.0',      '31.255.255.255', '128.0.0.0','159.255.255.255','3'],
            ['130.0.0.0/240.0.0.0',      '240.0.0.0',      '15.255.255.255', '128.0.0.0','143.255.255.255','4'],
            ['130.0.0.0/248.0.0.0',      '248.0.0.0',      '7.255.255.255',  '128.0.0.0','135.255.255.255','5'],
            ['130.0.0.0/252.0.0.0',      '252.0.0.0',      '3.255.255.255',  '128.0.0.0','131.255.255.255','6'],
            ['128.0.0.0/254.0.0.0',      '254.0.0.0',      '1.255.255.255',  '128.0.0.0','129.255.255.255','7'],
            ['127.0.0.0/255.0.0.0',      '255.0.0.0',      '0.255.255.255',  '127.0.0.0','127.255.255.255','8'],
            ['127.0.0.0/255.128.0.0',    '255.128.0.0',    '0.127.255.255',  '127.0.0.0','127.127.255.255','9'],
            ['127.0.0.0/255.192.0.0',    '255.192.0.0',    '0.63.255.255',   '127.0.0.0','127.63.255.255', '10'],
            ['127.0.0.0/255.224.0.0',    '255.224.0.0',    '0.31.255.255',   '127.0.0.0','127.31.255.255', '11'],
            ['127.0.0.0/255.240.0.0',    '255.240.0.0',    '0.15.255.255',   '127.0.0.0','127.15.255.255', '12'],
            ['127.0.0.0/255.248.0.0',    '255.248.0.0',    '0.7.255.255',    '127.0.0.0','127.7.255.255',  '13'],
            ['127.0.0.0/255.252.0.0',    '255.252.0.0',    '0.3.255.255',    '127.0.0.0','127.3.255.255',  '14'],
            ['127.0.0.0/255.254.0.0',    '255.254.0.0',    '0.1.255.255',    '127.0.0.0','127.1.255.255',  '15'],
            ['127.0.0.0/255.255.0.0',    '255.255.0.0',    '0.0.255.255',    '127.0.0.0','127.0.255.255',  '16'],
            ['127.0.0.0/255.255.128.0',  '255.255.128.0',  '0.0.127.255',    '127.0.0.0','127.0.127.255',  '17'],
            ['127.0.0.0/255.255.192.0',  '255.255.192.0',  '0.0.63.255',     '127.0.0.0','127.0.63.255',   '18'],
            ['127.0.0.0/255.255.224.0',  '255.255.224.0',  '0.0.31.255',     '127.0.0.0','127.0.31.255',   '19'],
            ['127.0.0.0/255.255.240.0',  '255.255.240.0',  '0.0.15.255',     '127.0.0.0','127.0.15.255',   '20'],
            ['127.0.0.0/255.255.248.0',  '255.255.248.0',  '0.0.7.255',      '127.0.0.0','127.0.7.255',    '21'],
            ['127.0.0.0/255.255.252.0',  '255.255.252.0',  '0.0.3.255',      '127.0.0.0','127.0.3.255',    '22'],
            ['127.0.0.0/255.255.254.0',  '255.255.254.0',  '0.0.1.255',      '127.0.0.0','127.0.1.255',    '23'],
            ['127.0.0.0/255.255.255.0',  '255.255.255.0',  '0.0.0.255',      '127.0.0.0','127.0.0.255',    '24'],
            ['127.0.0.0/255.255.255.128','255.255.255.128','0.0.0.127',      '127.0.0.0','127.0.0.127',    '25'],
            ['127.0.0.0/255.255.255.192','255.255.255.192','0.0.0.63',       '127.0.0.0','127.0.0.63',     '26'],
            ['127.0.0.0/255.255.255.224','255.255.255.224','0.0.0.31',       '127.0.0.0','127.0.0.31',     '27'],
            ['127.0.0.0/255.255.255.240','255.255.255.240','0.0.0.15',       '127.0.0.0','127.0.0.15',     '28'],
            ['127.0.0.0/255.255.255.248','255.255.255.248','0.0.0.7',        '127.0.0.0','127.0.0.7',      '29'],
            ['127.0.0.0/255.255.255.252','255.255.255.252','0.0.0.3',        '127.0.0.0','127.0.0.3',      '30'],
            ['127.0.0.0/255.255.255.254','255.255.255.254','0.0.0.1',        '127.0.0.0','127.0.0.1',      '31'],
            ['127.0.0.0/255.255.255.255','255.255.255.255','0.0.0.0',        '127.0.0.0','127.0.0.0',      '32'],
        ];
    }

    public function validIPv6Blocks()
    {
        return [
            //CIDR                               Mask                    Delta                               First IP          Last IP
            ['2001:0db8::/30',                   'ffff:fffc::',          '0:3:ffff:ffff:ffff:ffff:ffff:ffff','2001:db8::',     '2001:dbb:ffff:ffff:ffff:ffff:ffff:ffff','30'],
            ['2001:0db8::/31',                   'ffff:fffe::',          '0:1:ffff:ffff:ffff:ffff:ffff:ffff','2001:db8::',     '2001:db9:ffff:ffff:ffff:ffff:ffff:ffff','31'],
            ['2001:0db8::/32',                   'ffff:ffff::',          '::ffff:ffff:ffff:ffff:ffff:ffff',  '2001:db8::',     '2001:db8:ffff:ffff:ffff:ffff:ffff:ffff','32'],
            ['2001:0db8:85a3::8a2e:0370:7334/64','ffff:ffff:ffff:ffff::','::ffff:ffff:ffff:ffff',            '2001:db8:85a3::','2001:db8:85a3:0:ffff:ffff:ffff:ffff',   '64'],
        ];
    }

    public function validBlocks()
    {
        return array_merge($this->validIPv4Blocks(),$this->validIPv6Blocks());
    }

    /**
     * @dataProvider validIPv4Blocks
     */
    public function testCreateIPv4($block)
    {
        $block = IPBlock::create($block);
        $this->assertEquals(4, $block->getVersion());
    }

    /**
     * @dataProvider validIPv6Blocks
     */
    public function testCreateIPv6($block)
    {
        $block = IPBlock::create($block);
        $this->assertEquals(6, $block->getVersion());
    }

    /**
     * @dataProvider validBlocks
     */
    public function testGetFirstIp($block)
    {
        $first_ip = func_get_arg(3);
        $block = IPBlock::create($block);
        $this->assertEquals($first_ip, $block->getFirstIp()->humanReadable());
    }

    /**
     * @dataProvider validBlocks
     */
    public function testGetNetworkAddress($block)
    {
        $network_address = func_get_arg(3);
        $block = IPBlock::create($block);
        $this->assertEquals($network_address, $block->getNetworkAddress()->humanReadable());
    }

    /**
     * @dataProvider validBlocks
     */
    public function testGetLastIp($block)
    {
        $last_ip = func_get_arg(4);
        $block = IPBlock::create($block);
        $this->assertEquals($last_ip, $block->getLastIp()->humanReadable());
    }

    /**
     * @dataProvider validBlocks
     */
    public function testGetBroadcastAddress($block)
    {
        $broadcast_address = func_get_arg(4);
        $block = IPBlock::create($block);
        $this->assertEquals($broadcast_address, $block->getBroadcastAddress()->humanReadable());
    }

    /**
     * @dataProvider validBlocks
     */
    public function testGetPrefixLength($block)
    {
        $prefix_length = func_get_arg(5);
        $block = IPBlock::create($block);
        $this->assertEquals($prefix_length, $block->getPrefixLength());
    }

    /**
     * @dataProvider validBlocks
     */
    public function testGetNetmask($block)
    {
        $netmask = func_get_arg(1);
        $block = IPBlock::create($block);
        $this->assertEquals($netmask, $block->getNetmask());
    }

    /**
     * @dataProvider validBlocks
     */
    public function testGetDelta($block)
    {
        $delta = func_get_arg(2);
        $block = IPBlock::create($block);
        $this->assertEquals($delta, $block->getDelta());
    }

    public function invalidBlocks()
    {
        return [
            ['127.0.2666.1/24'],
            ['127.0.0.1/45'],
            ['127.0.0.1/255.255.2555.0'],
            ['127.0.0.1/junk'],
            ['127.0.0.1/'],
            ['127.0.0.1'],
            ["\t"],
            ['abc'],
            [12.3],
            [-12.3],
            ['-1'],
            ['4294967296'],
            ['2a01:8200::'],
            ['::1'],
        ];
    }

    /**
     * @dataProvider invalidBlocks
     */
    public function testCreateInvalid($block)
    {
        $this->expectException(\InvalidArgumentException::class);

        $instance = IPBlock::create($block);
    }

    /**
     * @dataProvider validIPv6Blocks
     */
    public function testConstructIPv4BlockWithIPv6Block($block)
    {
        $this->expectException(\InvalidArgumentException::class);

        $block = new IPv4Block($block);
    }

    /**
     * @dataProvider validIPv4Blocks
     */
    public function testConstructIPv6BlockWithIPv4Block($block)
    {
        $this->expectException(\InvalidArgumentException::class);

        $block = new IPv6Block($block);
    }

    /**
     * Block notations that don't start with the first IP of the block
     */
    public function misalignedBlocks()
    {
        return [
            // CIDR          // given IP      // actual block
            ['192.168.0.1/24', '192.168.0.1', '192.168.0.0/24'],
            ['192.168.1.42/24','192.168.1.42','192.168.1.0/24'],
        ];
    }

    /**
     * @dataProvider misalignedBlocks
     */
    public function testConstructMisalignedBlock($cidr, $given_ip, $actual_cidr)
    {
        $block = IPBlock::create($cidr);
        $this->assertEquals($actual_cidr, $block);
        $this->assertEquals($cidr, $block->getGivenIpWithPrefixLength());

        $this->assertEquals($given_ip, $block->getGivenIp()->humanReadable());
    }


    public function validOperations()
    {
        return [
            // block           plus/minus   result
            ['192.168.0.0/24', 0,           '192.168.0.0/24'],
            ['192.168.0.0/24', '0',         '192.168.0.0/24'],
            ['192.168.0.0/24', 5,           '192.168.5.0/24'],
            ['192.168.0.0/24', '5',         '192.168.5.0/24'],
            ['192.168.0.0/24', gmp_init(5), '192.168.5.0/24'],
            ['192.168.5.0/24', -5,          '192.168.0.0/24'],
            ['192.168.5.0/24', '-5',        '192.168.0.0/24'],
            ['192.168.5.0/24', gmp_init(-5),'192.168.0.0/24'],
            ['192.168.0.0/24', 256,         '192.169.0.0/24'],
            ['0.0.0.0/1',      1,           '128.0.0.0/1'],

            ['2001::/64',      '281474976710656', '2002::/64']
        ];
    }

    /**
     * @dataProvider validOperations
     */
    public function testPlusMinus($block, $plus, $result)
    {
        $block = IPBlock::create($block);
        
        $this->assertEquals($result, (string) $block->plus($plus), "$block + $plus = $result");
        $this->assertEquals((string) $block, (string) IPBlock::create($result)->minus($plus), "$result - $plus = $block");
    }

    public function oobAdditions()
    {
        return [
            // IP                 plus
            ['255.255.255.255/32', 1],
            ['255.255.255.254/32', 2],
            ['0.0.0.0/0',          1],
            ['0.0.0.0/0',         -1],
            ['0.0.0.0/1',          2],
            ['0.0.0.0/32',        -1],
            ['0.0.0.1/32',        -2],
        ];
    }

    /**
     * @dataProvider oobAdditions
     */
    public function testPlusOob($block, $plus)
    {
        $this->expectException(\OutOfBoundsException::class);

        $block = IPBlock::create($block);
        $block->plus($plus);
    }

    public function oobSubtractions()
    {
        return [
            // IP                 minus
            ['0.0.0.0/0',         1],
            ['0.0.0.0/32',        1],
            ['0.0.0.1/32',        2],
        ];
    }

    /**
     * @dataProvider oobSubtractions
     */
    public function testMinusOob($block, $minus)
    {
        $this->expectException(\OutOfBoundsException::class);

        $block = IPBlock::create($block);
        $block->minus($minus);
    }

    public function invalidPlusMinusValues()
    {
        return [
            [null],
            [array()],
            [2.5],
            ['junk'],
            ['127.63.255.255'],
            [new \stdClass()],
        ];
    }

    /**
     * @dataProvider invalidPlusMinusValues
     */
    public function testPlusInvalidArgument($value)
    {
        $this->expectException(\InvalidArgumentException::class);

        $block = IPBlock::create('127.0.0.0/24');
        $block->plus($value);
    }

    /**
     * @dataProvider invalidPlusMinusValues
     */
    public function testMinusInvalidArgument($value)
    {
        $this->expectException(\InvalidArgumentException::class);

        $block = IPBlock::create('127.0.0.0/24');
        $block->minus($value);
    }

    public function blockContent()
    {
        return [
            [
                '192.168.0.0/24',
                'in (ip)' => ['192.168.0.0', '192.168.0.42', '192.168.0.255'],
                'in (blocks)' => ['192.168.0.128/25'],
                'not in (ip)' => ['10.0.0.1', '192.167.255.255', '192.169.0.0'],
                'not in (block)' => ['10.0.0.1/24'],
            ],
            [
                '2001:0db8::/32',
                'in (ip)' => ['2001:db8::', '2001:0db8:85a3::8a2e:0370:7334', '2001:db8:ffff:ffff:ffff:ffff:ffff:ffff'],
                'in (blocks)' => ['2001:db8::/64'],
                'not in (ip)' => ['::1'],
                'not in (block)' => ['::1/128'],
            ],
        ];
    }

    /**
     * @dataProvider blockContent
     */
    public function testContains($block, $ip_in, $block_in, $ip_not_in, $block_not_in)
    {
        $block = IPBlock::create($block);
        foreach ($ip_in as $obj) {
            $this->assertTrue($block->contains($obj), "$block contains $obj");
            $this->assertTrue(IP::create($obj)->isIn($block), "$obj is in $block");
        }
        foreach ($block_in as $obj) {
            $this->assertTrue($block->contains($obj), "$block contains $obj");
            $this->assertTrue(IPBlock::create($obj)->isIn($block), "$obj is in $block");
            $this->assertFalse(IPBlock::create($obj)->contains($block), "$obj does not contain $block");
        }
        foreach ($ip_not_in as $obj) {
            $this->assertFalse($block->contains($obj), "$block does not contain $obj");
            $this->assertFalse(IP::create($obj)->isIn($block), "$obj is not in $block");
        }
        foreach ($block_not_in as $obj) {
            $this->assertFalse($block->contains($obj), "$obj is not in $block");
        }
    }

    public function overlappingBlocks()
    {
        return [
            [
                '192.168.0.0/24',
                'overlapping' => ['192.168.0.128/25', '192.168.0.0/23'],
                'not overlapping' => ['10.0.0.1/24'],
            ],
            [
                '2001:0db8::/32',
                'overlapping' => ['2001:0db8::/33', '2001:0db8::/32'],
                'not overlapping' => ['::1/128'],
            ],
        ];
    }

    /**
     * @dataProvider overlappingBlocks
     */
    public function testOverlaps($block, $overlapping, $not_overlapping)
    {
        $block = IPBlock::create($block);
        foreach ($overlapping as $block2) {
            $this->assertTrue($block->overlaps($block2), "$block is overlapping $block2");
            $this->assertTrue(IPBlock::create($block2)->overlaps($block), "$block2 is overlapping $block");
        }
        foreach ($not_overlapping as $block2) {
            $this->assertFalse($block->overlaps($block2), "$block is not overlapping $block2");
            $this->assertFalse(IPBlock::create($block2)->overlaps($block), "$block2 is not overlappping $block");
        }
    }


    public function invalidBlockCombinations()
    {
        return [
            'IPv4Block with IPv6' => ['192.168.0.0/24', '2001:db8::'],
            'IPv4Block with IPv6Block' => ['192.168.0.0/24', '2001:db8::/24'],
            'IPv6Block with IPv4' => ['2001:db8::/24', '192.168.0.0'],
            'IPv6Block with IPv4Block' => ['2001:db8::/24', '192.168.0.0/24'],
        ];
    }

    /**
     * @dataProvider invalidBlockCombinations
     */
    public function testContainsCannotMixIpVersions($block, $ip_or_block)
    {
        $this->expectException(\InvalidArgumentException::class);
        $block = IPBlock::create($block);
        $block->contains($ip_or_block);
    }

    /**
     * @dataProvider invalidBlockCombinations
     */
    public function testOverlapsCannotMixIpVersions($block, $ip_or_block)
    {
        $this->expectException(\InvalidArgumentException::class);
        $block = IPBlock::create($block);
        $block->overlaps($ip_or_block);
    }

    public function blocksWithSize(): array
    {
        return [
            ['192.168.0.0/24', 256],
            ['0.0.0.0/0', 4294967296],
            ['192.168.0.10/24', 256],
            ['::1/124', 16],
            ['::f:0/112', 65536],
            ['2001:acad::0/109', 524288],
            ['::1/128', 1],
            ['0.0.0.0/8', 16777216],
        ];
    }

    /**
     * @dataProvider blocksWithSize
     *
     * @param string $subnet
     * @param int    $expectedCount
     */
    public function testCountable(string $subnet, int $expectedCount)
    {
        $block = IPBlock::create($subnet);
        $this->assertCount($expectedCount, $block);
    }

    public function oversizedBlocks(): array
    {
        return [
            ['ffff::1/64'],
            ['aaaa::1/60'],
            ['b::/10'],
        ];
    }

    /**
     * @dataProvider oversizedBlocks
     */
    public function testCountableThrowsExceptionWhenSizeIsTooBig($subnet)
    {
        $this->expectException(\RuntimeException::class);

        $block = IPBlock::create($subnet);
        count($block);
    }

    /**
     * @dataProvider blocksWithSize
     */
    public function testGetNbAddresses(string $subnet, int $size)
    {
        $block = IPBlock::create($subnet);
        $this->assertEquals($size, $block->getNbAddresses());
    }

    public function testArrayAccess()
    {
        $block = IPBlock::create('192.168.0.0/24');
        $this->assertEquals('192.168.0.0', $block[0]);
        $this->assertEquals('192.168.0.15', $block[15]);
        $this->assertEquals('192.168.0.255', $block[255]);
    }

    public function testArrayAccessOobException()
    {
        $this->expectException(\OutOfBoundsException::class);
        $block = IPBlock::create('192.168.0.0/24');
        $block[256];
    }

    public function testArrayAccessSetterDisabled()
    {
        $this->expectException(\LogicException::class);
        $block = IPBlock::create('192.168.0.0/24');
        $block[2] = 'X';
    }

    public function testGetSubBlocks()
    {
        $block = IPBlock::create('192.168.8.0/24');
        $subnets = $block->getSubBlocks('/28');

        $this->assertCount(16, $subnets);
        $this->assertEquals('192.168.8.0', $subnets->current()->getFirstIp()->humanReadable());
        $this->assertEquals(28, $subnets->current()->getPrefixLength());

        $subnets->next();
        $subnets->next();

        $this->assertEquals('192.168.8.32/28', $subnets->current()->getGivenIpWithPrefixLength());
    }

    public function validSuperBlocks()
    {
        return [
            ['192.168.42.0/24', '/16', '192.168.0.0/16'],
            ['192.168.42.0/24', '16', '192.168.0.0/16'],
            ['192.168.42.0/24', 16, '192.168.0.0/16'],
        ];
    }

    /**
     * @dataProvider validSuperBlocks
     */
    public function testGetSuperBlock($block, $prefix_length, $superblock)
    {
        $block = IPBlock::create('192.168.42.0/24');
        $this->assertEquals($superblock, (string) $block->getSuperBlock($prefix_length));
    }

    public function invalidSuperBlockPrefixLengths()
    {
        return [
            ['192.168.42.0/24', ''],
            ['192.168.42.0/24', array()],
            ['192.168.42.0/24', new \stdClass()],
            ['192.168.42.0/24', null],
            ['192.168.42.0/24', 2.5],
            ['192.168.42.0/24', -1],
            ['192.168.42.0/24', '/32'],
        ];
    }

    /**
     * @dataProvider invalidSuperBlockPrefixLengths
     */
    public function testGetSuperBlockInvalidPrefixLength($block, $prefix_length)
    {
        $this->expectException(\InvalidArgumentException::class);
        $block = IPBlock::create($block);
        $block->getSuperBlock($prefix_length);
    }
}
