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

use PhpIP\IPv4Block;
use PHPUnit\Framework\TestCase;

class IPv4BlockTest extends TestCase
{
    public function testIterator()
    {
        $expectation = [
            '192.168.0.0',
            '192.168.0.1',
            '192.168.0.2',
            '192.168.0.3',
            '192.168.0.4',
            '192.168.0.5',
            '192.168.0.6',
            '192.168.0.7',
            '192.168.0.8',
            '192.168.0.9',
            '192.168.0.10',
            '192.168.0.11',
            '192.168.0.12',
            '192.168.0.13',
            '192.168.0.14',
            '192.168.0.15',
        ];

        $subnet = new IPv4Block('192.168.0.0/28');

        $this->assertEquals($expectation, iterator_to_array($subnet->getIterator()));
    }

    public function testGetPrivateBlocks()
    {
        $private_blocks = IPv4Block::getPrivateBlocks();

        $this->assertInstanceOf(IPv4Block::class, $private_blocks[0]);
        $this->assertEquals('10.0.0.0/8', (string) $private_blocks[0]);
        $this->assertCount(6, $private_blocks);
    }

    public function testGetLoopbackBlock()
    {
        $loopback_block = IPv4Block::getLoopbackBlock();

        $this->assertInstanceOf(IPv4Block::class, $loopback_block);
        $this->assertEquals('127.0.0.0/8', (string) $loopback_block);
    }

    public function testGetLinkLocalBlock()
    {
        $link_local_block = IPv4Block::getLinkLocalBlock();

        $this->assertInstanceOf(IPv4Block::class, $link_local_block);
        $this->assertEquals('169.254.0.0/16', (string) $link_local_block);
    }

    public function testGetReservedBlocks()
    {
        $reserved_blocks = IPv4Block::getReservedBlocks();

        $this->assertInstanceOf(IPv4Block::class, $reserved_blocks[0]);
        $this->assertEquals('0.0.0.0/8', (string) $reserved_blocks[0]);
    }
}
