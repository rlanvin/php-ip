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
use PhpIP\IP;
use PHPUnit\Framework\TestCase;

class IPBlockTest extends TestCase
{
    public function validOperations()
    {
        return [
            // block           plus  minus  result
            ['192.168.0.0/24', 5,    null, '192.168.5.0/24'],
            ['192.168.0.0/24', 256,  null, '192.169.0.0/24'],
            ['0.0.0.0/1',      1,    null, '128.0.0.0/1'],
        ];
    }

    /**
     * @dataProvider validOperations
     */
    public function testPlusMinus($block, $plus, $minus, $result)
    {
        $block = IPBlock::create($block);
        if ($plus !== null) {
            $this->assertEquals($result, (string) $block->plus($plus), "$block + $plus = $result");
            $this->assertEquals((string) $block, (string) IPBlock::create($result)->minus($plus), "$result - $plus = $block");
        } elseif ($minus !== null) {
            $this->assertEquals($result, (string) $block->minus($minus), "$block - $minus = $result");
            $this->assertEquals((string) $block, (string) IPBlock::create($result)->plus($minus), "$result + $minus = $block");
        }
    }

    public function invalidOperations()
    {
        return [
            // IP                 plus minus
            ['255.255.255.255/32', 1, null],
            ['255.255.255.254/32', 2, null],
            ['0.0.0.0/0',          1, null],
            ['0.0.0.0/0',         -1, null],
            ['0.0.0.0/1',          2, null],
            ['0.0.0.0/32',        -1, null],
            ['0.0.0.1/32',        -2, null],
        ];
    }

    /**
     * @dataProvider invalidOperations
     */
    public function testPlusMinusOob($block, $plus, $minus)
    {
        $this->expectException(\OutOfBoundsException::class);

        $block = IPBlock::create($block);
        if ($plus !== null) {
            $block->plus($plus);
        } elseif ($minus !== null) {
            $block->minus($minus);
        }
    }

    public function blockContent()
    {
        return [
            [
                '192.168.0.0/24',
                ['192.168.0.0', '192.168.0.42', '192.168.0.255'],
                ['192.168.0.128/25'],
                ['10.0.0.1', '192.167.255.255', '192.169.0.0'],
                ['10.0.0.1/24'],
            ],
            [
                '2001:0db8::/32',
                ['2001:db8::', '2001:0db8:85a3::8a2e:0370:7334', '2001:db8:ffff:ffff:ffff:ffff:ffff:ffff'],
                ['2001:db8::/64'],
                ['::1'],
                ['::1/128'],
            ],
        ];
    }

    /**
     * @dataProvider blockContent
     */
    public function testContains($block, $ip_in, $block_in, $ip_not_in, $block_not_in)
    {
        $block = IPBlock::create($block);
        foreach ($ip_in as $ip) {
            $this->assertTrue($block->contains($ip), "$block contains $ip");
            $this->assertTrue(IP::create($ip)->isIn($block), "$ip is in $block");
        }
        foreach ($block_in as $ip) {
            $this->assertTrue($block->contains($ip), "$block contains $ip");
            $this->assertFalse(IPBlock::create($ip)->contains($block), "$ip does not contain $block");
        }
        foreach ($ip_not_in as $ip) {
            $this->assertFalse($block->contains($ip), "$block does not contain $ip");
            $this->assertFalse(IP::create($ip)->isIn($block), "$ip is not in $block");
        }
        foreach ($block_not_in as $ip) {
            $this->assertFalse($block->contains($ip), "$ip is not in $block");
        }
    }

    public function overlappingBlocks()
    {
        return [
            [
                '192.168.0.0/24',
                ['192.168.0.128/25', '192.168.0.0/23'],
                ['10.0.0.1/24'],
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

    public function getIpBlockCounts(): array
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
     * @dataProvider getIpBlockCounts
     *
     * @param string $subnet
     * @param int    $expectedCount
     */
    public function testCountable(string $subnet, int $expectedCount)
    {
        $block = IPBlock::create($subnet);
        $this->assertCount($expectedCount, $block);
    }

    /**
     * @return array
     */
    public function getOversizeAddressBlocks(): array
    {
        return [
            ['ffff::1/64'],
            ['aaaa::1/60'],
            ['b::/10'],
        ];
    }

    /**
     * @dataProvider getOversizeAddressBlocks
     */
    public function testCountableThrowsException($subnet)
    {
        $this->expectException(\RuntimeException::class);

        $block = IPBlock::create($subnet);
        count($block);
    }

    /**
     * @return array
     */
    public function getAddressBlocksWithSizes(): array
    {
        return [
            ['0.0.0.0/0', '4294967296'],
            ['192.168.0.10/24', '256'],
            ['::1/124', '16'],
            ['::f:0/112', '65536'],
        ];
    }

    /**
     * @dataProvider getAddressBlocksWithSizes
     *
     * @param string $subnet
     * @param string $size
     */
    public function testGetNbAddresses(string $subnet, string $size)
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
        try {
            $block[256];
            $this->fail('[] shoud throw OutOfBoundException');
        } catch (\OutOfBoundsException $e) {
        }

        try {
            $block[2] = 'X';
            $this->fail('Setting with [] shoud throw LogicException');
        } catch (\LogicException $e) {
        }
    }

    public function testGetSubBlocks()
    {
        $block = IPBlock::create('192.168.8.0/24');
        $subnets = $block->getSubBlocks('/28');

        $this->assertCount(16, $subnets);
        $this->assertEquals('192.168.8.0', $subnets->current()->getFirstIp()->humanReadable());
        $this->assertEquals(28, $subnets->current()->getPrefix());

        $subnets->next();
        $subnets->next();

        $this->assertEquals('192.168.8.32/28', $subnets->current()->getGivenIpWithPrefixLen());
    }

    public function testGetSuperBlock()
    {
        $block = IPBlock::create('192.168.42.0/24');
        $this->assertEquals('192.168.0.0/16', (string) $block->getSuperBlock('/16'));

        try {
            $block->getSuperBlock('');
            $this->fail('Expected InvalidArgumentException has not be thrown');
        } catch (\InvalidArgumentException $e) {
        }

        try {
            $block->getSuperBlock('/32');
            $this->fail('Expected InvalidArgumentException has not be thrown');
        } catch (\InvalidArgumentException $e) {
        }
    }
}
