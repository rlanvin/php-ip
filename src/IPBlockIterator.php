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

namespace PhpIP;

/**
 * Iterator for IPBlock. This could be a Generator in PHP 5.5.
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
    protected $current_block = null;

    /**
     * @var IPBlock
     */
    protected $first_block = null;

    /**
     * @var \GMP
     */
    protected $nb_blocks;

    /**
     * @var string
     */
    protected $class = '';

    /**
     * IPBlockIterator constructor.
     *
     * @param IPBlock $first_block
     * @param \GMP    $nb_blocks
     */
    public function __construct(IPBlock $first_block, \GMP $nb_blocks)
    {
        $this->class = get_class($first_block);

        $this->first_block = $first_block;
        $this->current_block = $first_block;
        $this->nb_blocks = $nb_blocks;
        $this->position = gmp_init(0);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        if (gmp_cmp($this->nb_blocks, PHP_INT_MAX) > 0) {
            throw new \RuntimeException('The number of address blocks is bigger than PHP_INT_MAX, use getNbBlocks() instead');
        }

        return gmp_intval($this->nb_blocks);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = gmp_init(0);
        $this->current_block = $this->first_block;
    }

    /**
     * {@inheritdoc}
     */
    public function current(): IPBlock
    {
        return $this->current_block;
    }

    /**
     * {@inheritdoc}
     */
    public function key(): \GMP
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->position = gmp_add($this->position, 1);
        $this->current_block = $this->current_block->plus(1);
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return gmp_cmp($this->position, 0) >= 0 && gmp_cmp($this->position, $this->nb_blocks) < 0;
    }

    /**
     * @return string
     */
    public function getNbBlocks(): string
    {
        return gmp_strval($this->nb_blocks);
    }
}
