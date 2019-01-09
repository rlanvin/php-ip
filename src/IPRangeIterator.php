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
 * Iterates over each IP in a single IPBlock.
 */
class IPRangeIterator implements \Iterator, \Countable
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

    public function rewind(): void
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

    public function next(): void
    {
        $this->position = gmp_add($this->position, 1);
    }

    public function valid(): bool
    {
        return gmp_cmp($this->position, 0) >= 0 && gmp_cmp($this->position, $this->ipBlock->getNbAddresses()) < 0;
    }

    public function count(): int
    {
        return $this->ipBlock->count();
    }
}
