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
 * Iterate over networks starting from an initial block.
 */
class IPBlockIterator implements \Iterator, \Countable
{
    /**
     * @var \GMP
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
     * @var \GMP
     */
    protected $nb_blocks;

    /**
     * IPBlockIterator constructor.
     *
     * @param IPBlock  $first_block
     * @param \GMP|int $nb_blocks
     */
    public function __construct(IPBlock $first_block, $nb_blocks)
    {
        if (!$nb_blocks instanceof \GMP) {
            $nb_blocks = gmp_init($nb_blocks);
        }

        $this->current_block = $this->first_block = $first_block;
        $this->nb_blocks = $nb_blocks;
        $this->position = gmp_init(0);
    }

    public function count(): int
    {
        $value = gmp_intval($this->nb_blocks);

        if ($value > PHP_INT_MAX) {
            throw new \RuntimeException();
        }

        return $value;
    }

    public function rewind()
    {
        $this->position = gmp_init(0);
        $this->current_block = $this->first_block;
    }

    public function current(): IPBlock
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
        return
            gmp_cmp($this->position, 0) >= 0 &&
            gmp_cmp($this->position, $this->nb_blocks) < 0;
    }
}
