<?php

/**
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 * @author Samuel Williams <sam@badcow.co>
 *
 * @see https://github.com/rlanvin/php-ip
 */

namespace phpIP\Tests;

use phpIP\IPBlock;
use phpIP\IPBlockIterator;

class IPBlockIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $ipBlocks = array(
        //[   "Starting block", "n", "nth block",]
        array('192.168.0.0/24', 5, '192.168.5.0/24'),
        array('172.16.2.0/23', 10, '172.16.22.0/23'),
        array('10.0.0.0/16', 8, '10.8.0.0/16'),
    );

    /**
     * @return array
     */
    public function getIPBlocks()
    {
        return $this->ipBlocks;
    }

    /**
     * @dataProvider getIPBlocks
     *
     * @param string $startingBlock
     * @param int    $n
     * @param string $endblock
     */
    public function testIterator($startingBlock, $n, $endblock)
    {
        $ipBlock = IPBlock::create($startingBlock);
        $iterator = new IPBlockIterator($ipBlock, $n);

        for ($i = 0; $i < $n; ++$i) {
            $iterator->next();
        }

        $this->assertEquals($endblock, $iterator->current()->getGivenIpWithPrefixlen());

        //Test the rewind
        $iterator->rewind();
        $this->assertEquals($startingBlock, $iterator->current()->getGivenIpWithPrefixlen());

        //Test the count
        $this->assertCount($n, $iterator);
    }

    /**
     * @dataProvider getIPBlocks
     *
     * @param string $startingBlock
     * @param int    $n
     */
    public function testValid($startingBlock, $n)
    {
        $ipBlock = IPBlock::create($startingBlock);
        $iterator = new IPBlockIterator($ipBlock, $n);

        for ($i = 0; $i < $n; ++$i) {
            $this->assertTrue($iterator->valid());
            $iterator->next();
        }

        $this->assertFalse($iterator->valid());
    }
}
