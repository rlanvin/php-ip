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
 * Iterator for IPBlock. This could be a Generator in PHP 5.5.
 */
class IPBlockIterator implements \Iterator, \Countable
{
    /**
     * @var resource|\GMP
     */
    protected $position;

    /**
     * @var IPBlock
     */
    protected $current_block;

    /**
     * @var IPBlock
     */
    protected $first_block;

    /**
     * @var int
     */
    protected $nb_blocks;

    /**
     * IPBlockIterator constructor.
     *
     * @param IPBlock $first_block
     * @param $nb_blocks
     */
    public function __construct(IPBlock $first_block, $nb_blocks)
    {
        $this->first_block = $this->current_block = $first_block;
        $this->nb_blocks = $nb_blocks;
        $this->position = gmp_init(0);
    }

    public function count()
    {
        return $this->nb_blocks;
    }

    public function rewind()
    {
        $this->position = gmp_init(0);
        $this->current_block = $this->first_block;
    }

    /**
     * @return IPBlock
     */
    public function current()
    {
        return $this->current_block;
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position = gmp_add($this->position, 1);
        $this->current_block = $this->current_block->plus(1);
    }

    public function valid()
    {
        return gmp_cmp($this->position, 0) >= 0 && gmp_cmp($this->position, $this->nb_blocks) < 0;
    }
}
