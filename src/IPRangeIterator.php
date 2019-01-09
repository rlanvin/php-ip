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

namespace phpIP;

/**
 * Base class to manipulate CIDR block (aka "networks").
 */
class IPRangeIterator implements \Iterator
{
    /**
     * @var \GMP
     */
    private $position;

    /**
     * @var IPBlock
     */
    private $ipBlock;

    /**
     * IPRangeIterator constructor.
     *
     * @param IPBlock $ipBlock
     */
    public function __construct(IPBlock $ipBlock)
   {
       $this->ipBlock = $ipBlock;
       $this->position = gmp_init(0);
   }

    public function rewind()
    {
        $this->position = gmp_init(0);
    }

    public function current(): IP
    {
        return $this->ipBlock->getFirstIp()->plus(gmp_strval($this->position));
    }

    public function key(): \GMP
    {
        return $this->position;
    }

    public function next()
    {
        $this->position = gmp_add($this->position, 1);
    }

    public function valid()
    {
        return gmp_cmp($this->position, 0) >= 0 && gmp_cmp($this->position, $this->ipBlock->getNbAddresses()) < 0;
    }
}
