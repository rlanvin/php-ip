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

namespace PhpIP;

/**
 * Iterator for IPBlock. This could be a Generator in PHP 5.5.
 */
class IPBlockIterator implements \Iterator
{
    /**
     * @var int|\GMP
     */
    protected $position = 0;

    /**
     * @var IPBlock
     */
    protected $current_block = null;

    /**
     * @var IPBlock
     */
    protected $first_block = null;

    /**
     * @var int
     */
    protected $nb_blocks = 0;

    /**
     * @var string
     */
    protected $class = '';

    public function __construct(IPBlock $first_block, $nb_blocks)
    {
        $this->class = get_class($first_block);

        $this->first_block = $first_block;
        $this->nb_blocks = $nb_blocks;
    }

    public function count()
    {
        return gmp_strval($this->nb_blocks);
    }

    public function rewind()
    {
        $this->position = gmp_init(0);
        $this->current_block = $this->first_block;
    }

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
