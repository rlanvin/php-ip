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

use PhpIP\IPBlockIterator;
use PhpIP\IPBlock;
use PHPUnit\Framework\TestCase;

class IPBlockIteratorTest extends TestCase
{
    public function validIterators()
    {
        return [
            // first block    blocks
            ['192.168.0.1/24','2',
                // content
                ['192.168.0.0/24','192.168.1.0/24']
            ],
        ];
    }

    /**
     * @dataProvider validIterators
     */
    public function testIterator($first_block, $nb_blocks, $content)
    {
        $iterator = new IPBlockIterator(IpBlock::create($first_block), gmp_init($nb_blocks));

        $this->assertEquals($content, iterator_to_array($iterator));
    }

    /**
     * @dataProvider validIterators
     */
    public function testArrayAccess($first_block, $nb_blocks, $content)
    {
        $iterator = new IPBlockIterator(IpBlock::create($first_block), gmp_init($nb_blocks));
        foreach ($content as $index => $block) {
            $this->assertEquals($block, $iterator[$index]);
            $this->assertEquals($block, $iterator[(string) $index]);
            $this->assertEquals($block, $iterator[gmp_init($index)]);
        }
    }

    public function oobIndexes()
    {
        return [
            ['192.168.0.1/24', 2, 3],
            ['192.168.0.1/24', 2, '3'],
            ['192.168.0.1/24', 2, gmp_init(3)],
            ['192.168.0.1/24', 2, -1],
            ['192.168.0.1/24', 2, '-1'],
            ['192.168.0.1/24', 2, gmp_init(-1)],
        ];
    }

    /**
     * @dataProvider oobIndexes
     */
    public function testArrayAccessOobException($first_block, $nb_blocks, $index)
    {
        $this->expectException(\OutOfBoundsException::class);
        
        $iterator = new IPBlockIterator(IPBlock::create($first_block), gmp_init($nb_blocks));
        $iterator[$index];
    }

    public function invalidOffsets()
    {
        return [
            ['a'],
            [2.5],
            ['255.255.0.0'],
            ['junk'],
            [array()],
            [new \stdClass],
            [null],
        ];
    }

    /**
     * @dataProvider invalidOffsets
     */
    public function testArrayAccessInvalidOffset($offset)
    {
        $this->expectException(\InvalidArgumentException::class);
        $iterator = new IPBlockIterator(IPBlock::create('192.168.0.1/24'), gmp_init(2));
        $iterator[$offset];
    }
}