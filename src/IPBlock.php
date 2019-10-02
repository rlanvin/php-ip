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
 * Base class to manipulate CIDR block (aka "networks").
 */
abstract class IPBlock implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var IP
     */
    protected $given_ip;

    /**
     * @var IP
     */
    protected $first_ip;

    /**
     * @var IP
     */
    protected $last_ip;

    /**
     * @var int
     */
    protected $prefix;

    /**
     * @var IP
     */
    protected $mask;

    /**
     * @var IP
     */
    protected $delta;

    /**
     * @var string Numeric string
     */
    protected $nb_addresses;

    /**
     * @var string Either "IPv4" or "IPv6"
     */
    protected static $ip_class;

    /**
     * Return netmask.
     *
     * @return IP
     */
    public function getMask(): IP
    {
        if ($this->mask === null) {
            if ($this->prefix == 0) {
                $this->mask = new static::$ip_class(0);
            } else {
                $max_int = gmp_init(static::$ip_class::MAX_INT);
                $mask = gmp_shiftl($max_int, static::$ip_class::NB_BITS - $this->prefix);
                $mask = gmp_and($mask, $max_int); // truncate to 128 bits only
                $this->mask = new static::$ip_class($mask);
            }
        }

        return $this->mask;
    }

    /**
     * Return delta to last IP address.
     *
     * @return IP
     */
    public function getDelta(): IP
    {
        if ($this->delta === null) {
            if ($this->prefix == 0) {
                $this->delta = new static::$ip_class(static::$ip_class::MAX_INT);
            } else {
                $this->delta = new static::$ip_class(gmp_sub(gmp_shiftl(1, static::$ip_class::NB_BITS - $this->prefix), 1));
            }
        }

        return $this->delta;
    }

    /**
     * @param mixed $ip
     * @param mixed $prefix
     *
     * @return IPv4Block|IPv6Block
     */
    public static function create($ip, $prefix = ''): IPBlock
    {
        try {
            return new IPv4Block($ip, $prefix);
        } catch (\InvalidArgumentException $e) {
            // do nothing
        }

        try {
            return new IPv6Block($ip, $prefix);
        } catch (\InvalidArgumentException $e) {
            // do nothing
        }

        throw new \InvalidArgumentException("$ip does not appear to be an IPv4 or IPv6 block");
    }

    /**
     * Accepts a CIDR string (e.g. 192.168.0.0/24) or an IP and a prefix as
     * two separate parameters.
     *
     * @param mixed $ip_or_cidr IP or CIDR string
     * @param mixed $prefix     int (optional) The "slash" part
     */
    public function __construct($ip_or_cidr, $prefix = '')
    {
        $this->given_ip = $ip_or_cidr;
        if (is_string($ip_or_cidr) && strpos($ip_or_cidr, '/') !== false) {
            list($this->given_ip, $prefix) = explode('/', $ip_or_cidr, 2);
        }

        if (!$this->given_ip instanceof IP) {
            $this->given_ip = new static::$ip_class($this->given_ip);
        }

        $this->prefix = $this->checkPrefix($prefix);

        $this->first_ip = $this->given_ip->bit_and($this->getMask());
        $this->last_ip = $this->first_ip->bit_or($this->getDelta());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->first_ip.'/'.$this->prefix;
    }

    /**
     * Returns given IP.
     *
     * For example 192.168.48.7 for 192.168.48.7/24
     *
     * @return IP
     */
    public function getGivenIp(): IP
    {
        return $this->given_ip;
    }

    /**
     * Returns the prefix (the slash part).
     *
     * @return int
     */
    public function getPrefix(): int
    {
        return $this->prefix;
    }

    /**
     * @return int
     */
    public function getMaxPrefix(): int
    {
        return static::$ip_class::NB_BITS;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return static::$ip_class::IP_VERSION;
    }

    /**
     * @param int $value
     *
     * @return IPBlock
     */
    public function plus(int $value): IPBlock
    {
        if ($value < 0) {
            return $this->minus(-1 * $value);
        }

        if ($value == 0) {
            return clone $this;
        }

        // check boundaries
        try {
            $first_ip = $this->first_ip->plus(gmp_mul($value, $this->getNbAddresses()));

            return new static(
                $first_ip,
                $this->prefix
            );
        } catch (\InvalidArgumentException $e) {
            throw new \OutOfBoundsException($e->getMessage());
        }
    }

    /**
     * @param int $value
     *
     * @return IPBlock
     */
    public function minus(int $value): IPBlock
    {
        if ($value < 0) {
            return $this->plus(-1 * $value);
        }

        if ($value == 0) {
            return clone $this;
        }

        // check boundaries
        try {
            $first_ip = $this->first_ip->minus(gmp_mul($value, $this->getNbAddresses()));

            return new static(
                $first_ip,
                $this->prefix
            );
        } catch (\InvalidArgumentException $e) {
            throw new \OutOfBoundsException($e->getMessage());
        }
    }

    /**
     * Returns the first IP address of the block.
     *
     * @return IP
     */
    public function getFirstIp(): IP
    {
        return $this->first_ip;
    }

    /**
     * Returns the last IP address of the block.
     *
     * @return IP
     */
    public function getLastIp(): IP
    {
        return $this->last_ip;
    }

    /**
     * Returns the Network IP address of the block (the first address).
     *
     * @see getFirstIp
     *
     * @return IP
     */
    public function getNetworkAddress(): IP
    {
        return $this->first_ip;
    }

    /**
     * Returns the Broadcast IP address of the block (the last address).
     *
     * @see getLastIp
     *
     * @return IP
     */
    public function getBroadcastAddress(): IP
    {
        return $this->last_ip;
    }

    /**
     * A string representation of the given IP with the mask in prefix notation.
     *
     * @return string
     */
    public function getGivenIpWithPrefixLen(): string
    {
        return $this->given_ip.'/'.$this->prefix;
    }

    /**
     * A string representation of the given IP with the network as a net mask.
     *
     * @return string
     **/
    public function getGivenIpWithNetmask(): string
    {
        return $this->given_ip.'/'.$this->getMask();
    }

    /**
     * @internal
     * Check if the prefix is valid
     *
     * @param mixed $prefix
     *
     * @throws \InvalidArgumentException
     * @return int
     */
    protected function checkPrefix($prefix)
    {
        if ($prefix === '' || $prefix === null || $prefix === false || $prefix < 0 || $prefix > $this->getMaxPrefix()) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid IPv%s block prefix '%s'",
                $this->getVersion(),
                $prefix
            ));
        }

        return (int) $prefix;
    }

    /**
     * Split the block into smaller blocks.
     *
     * Returns an iterator, use foreach to loop it and count to get number of subnets.
     *
     * @param mixed $prefix
     *
     * @return IPBlockIterator
     */
    public function getSubBlocks($prefix): IPBlockIterator
    {
        $prefix = ltrim($prefix, '/');
        $prefix = $this->checkPrefix($prefix);

        if ($prefix <= $this->prefix) {
            throw new \InvalidArgumentException("Prefix must be smaller than {$this->prefix} ($prefix given)");
        }

        $first_block = new static($this->first_ip, $prefix);
        $number_of_blocks = gmp_pow(2, $prefix - $this->prefix);

        return new IPBlockIterator($first_block, $number_of_blocks);
    }

    /**
     * @deprecated since version 2.0 and will be removed in 3.0. Use IPBlock::getSuperBlock() instead.
     *
     * @param mixed $prefix
     *
     * @return IPBlock
     */
    public function getSuper($prefix): IPBlock
    {
        @trigger_error('IPBlock::getSuper() is deprecated since version 2.0 and will be removed in 3.0. Use IPBlock::getSuperBlock() instead.', E_USER_DEPRECATED);

        return $this->getSuperBlock($prefix);
    }

    /**
     * Return the super block containing the current block.
     *
     * @param mixed $prefix
     *
     * @return IPBlock
     */
    public function getSuperBlock($prefix): IPBlock
    {
        $prefix = ltrim($prefix, '/');
        $prefix = $this->checkPrefix($prefix);

        if ($prefix >= $this->prefix) {
            throw new \InvalidArgumentException("Prefix must be bigger than {$this->prefix} ($prefix given)");
        }

        return new static($this->first_ip, $prefix);
    }

    /**
     * Determine if the current block contains an IP address or block.
     *
     * @param mixed $ip_or_block
     *
     * @return bool
     */
    public function contains($ip_or_block): bool
    {
        if ((is_string($ip_or_block) && strpos($ip_or_block, '/') !== false) || $ip_or_block instanceof IPBlock) {
            return $this->containsBlock($ip_or_block);
        } else {
            return $this->containsIP($ip_or_block);
        }
    }

    /**
     * Determine if the current block contains an IP address.
     *
     * @param mixed $ip
     *
     * @return bool
     */
    public function containsIP($ip): bool
    {
        if (!$ip instanceof IP) {
            $ip = IP::create($ip);
        }

        return ($ip->numeric() >= $this->getFirstIp()->numeric()) && ($ip->numeric() <= $this->getLastIp()->numeric());
    }

    /**
     * Determine if the current block contains another block.
     *
     * True in this situation:
     * $this: first_ip[                               ]last_ip
     * $block:         first_ip[             ]last_ip
     *
     * @param mixed $block
     *
     * @return bool
     */
    public function containsBlock($block): bool
    {
        if (!$block instanceof IPBlock) {
            $block = new static($block);
        }

        return $block->getFirstIp()->numeric() >= $this->first_ip->numeric() && $block->getLastIp()->numeric() <= $this->last_ip->numeric();
    }

    /**
     * Determine if the current block is contained in another block.
     *
     * @param mixed $block
     *
     * @return bool
     */
    public function isIn($block): bool
    {
        if (!$block instanceof IPBlock) {
            $block = new static($block);
        }

        return $block->containsBlock($this);
    }

    /**
     * Test is the two blocks overlap, i.e. if block1 contains block2, or block2 contains block1.
     *
     * @param mixed $block
     *
     * @return bool
     */
    public function overlaps($block): bool
    {
        if (!$block instanceof IPBlock) {
            $block = new static($block);
        }

        return !($block->getFirstIp()->numeric() > $this->last_ip->numeric() || $block->getLastIp()->numeric() < $this->first_ip->numeric());
    }

    /**
     * Return the number of IP addresses in the block.
     *
     * @return string numeric string (can be huge)
     */
    public function getNbAddresses(): string
    {
        if ($this->nb_addresses === null) {
            $this->nb_addresses = gmp_strval(gmp_pow(2, $this->getMaxPrefix() - $this->prefix));
        }

        return $this->nb_addresses;
    }

    /**
     * Count the number of addresses contained with the address block. May exceed PHP's internal maximum integer.
     *
     * @return int
     *
     * @throws \RuntimeException thrown if the number of addresses exceeds PHP_INT_MAX
     */
    public function count(): int
    {
        $network_size = gmp_init($this->getNbAddresses());
        if (gmp_cmp($network_size, PHP_INT_MAX) > 0) {
            throw new \RuntimeException('The number of addresses is bigger than PHP_INT_MAX, use getNbAddresses() instead');
        }

        return gmp_intval($network_size);
    }

    /**
     * @return \Generator|IP[]
     */
    public function getIterator(): \Generator
    {
        $position = gmp_init(0);

        while (gmp_cmp($position, 0) >= 0 && gmp_cmp($position, $this->getNbAddresses()) < 0) {
            yield $this->first_ip->plus(gmp_strval($position));
            $position = gmp_add($position, 1);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return gmp_cmp($offset, 0) >= 0 && gmp_cmp($offset, $this->getNbAddresses()) < 0;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset): IP
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException("Offset $offset does not exists");
        }

        return $this->first_ip->plus($offset);
    }

    /**
     * Method is logically unsupported.
     *
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Setting IP in block is not supported');
    }

    /**
     * Method is logically unsupported.
     *
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException('Unsetting IP in block is not supported');
    }
}
