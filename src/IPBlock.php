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
    protected $prefix_length;

    /**
     * @var IP
     */
    protected $netmask;

    /**
     * @var IP
     */
    protected $delta;

    /**
     * @var string Numeric string
     */
    protected $nb_addresses;

    public function getMask(): IP
    {
        trigger_error(__METHOD__." is deprecated, get getNetmask instead", E_USER_DEPRECATED);
        return $this->getNetmask();
    }

    /**
     * Return netmask.
     *
     * @return IP
     */
    public function getNetmask(): IP
    {
        if ($this->netmask === null) {
            $ip_class = static::IP_CLASS;
            if ($this->prefix_length == 0) {
                $this->netmask = new $ip_class(0);
            } else {
                $max_int = gmp_init($ip_class::MAX_INT);
                $netmask = gmp_shiftl($max_int, $ip_class::NB_BITS - $this->prefix_length);
                $netmask = gmp_and($netmask, $max_int); // truncate to 128 bits only
                $this->netmask = new $ip_class($netmask);
            }
        }

        return $this->netmask;
    }

    /**
     * Return delta to last IP address (also known as "wildcard", "hostmask")
     *
     * @return IP
     */
    public function getDelta(): IP
    {
        if ($this->delta === null) {
            $ip_class = static::IP_CLASS;
            if ($this->prefix_length == 0) {
                $this->delta = new $ip_class($ip_class::MAX_INT);
            } else {
                $this->delta = new $ip_class(gmp_sub(gmp_shiftl(1, $ip_class::NB_BITS - $this->prefix_length), 1));
            }
        }

        return $this->delta;
    }

    /**
     * @param mixed $ip
     * @param mixed $prefix_length
     *
     * @return IPv4Block|IPv6Block
     */
    public static function create($ip, $prefix_length = ''): IPBlock
    {
        try {
            return new IPv4Block($ip, $prefix_length);
        } catch (\InvalidArgumentException $e) {
            // do nothing
        }

        try {
            return new IPv6Block($ip, $prefix_length);
        } catch (\InvalidArgumentException $e) {
            // do nothing
        }

        throw new \InvalidArgumentException("$ip does not appear to be an IPv4 or IPv6 block");
    }

    /**
     * Accepts a CIDR string (e.g. 192.168.0.0/24) or an IP and a prefix length as
     * two separate parameters.
     *
     * @param mixed $ip_or_cidr      IP or CIDR string
     * @param mixed $prefix_length   int (optional) The prefix length (after the /)
     */
    public function __construct($ip_or_cidr, $prefix_length = '')
    {
        $this->given_ip = $ip_or_cidr;
        if (is_string($ip_or_cidr) && strpos($ip_or_cidr, '/') !== false) {
            list($this->given_ip, $prefix_length) = explode('/', $ip_or_cidr, 2);
        }

        if (!$this->given_ip instanceof IP) {
            $ip_class = static::IP_CLASS;
            $this->given_ip = new $ip_class($this->given_ip);
        }

        $this->prefix_length = $this->checkPrefixLength($prefix_length);

        $this->first_ip = $this->given_ip->bit_and($this->getNetmask());
        $this->last_ip = $this->first_ip->bit_or($this->getDelta());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->withPrefixLength();
    }

    /**
     * Returns the IP given in the constructor.
     * For the first IP of the block, see getFirstIp()
     *
     * For example 192.168.48.7 for 192.168.48.7/24
     *
     * @see getFirstIp()
     * @return IP
     */
    public function getGivenIp(): IP
    {
        return $this->given_ip;
    }

    /**
     * @deprecated
     */
    public function getPrefix(): int
    {
        trigger_error(__METHOD__." is deprecated and will be removed, use getPrefixLength() instead.", E_USER_DEPRECATED);
        return $this->getPrefixLength();
    }

    /**
     * Returns the prefix length (the slash part).
     *
     * @return int
     */
    public function getPrefixLength(): int
    {
        return $this->prefix_length;
    }

    /**
     * @deprecated
     */
    public function getMaxPrefix(): int
    {
        trigger_error(__METHOD__." is deprecated and will be removed, use getMaxPrefixLength() instead.", E_USER_DEPRECATED);
        return $this->getMaxPrefixLength();
    }

    /**
     * Returns the max prefix length allowed by the version of IP
     *
     * @return int
     */
    public function getMaxPrefixLength(): int
    {
        return (static::IP_CLASS)::NB_BITS;
    }

    /**
     * Return the IP version of the block.
     *
     * @return int
     */
    public function getVersion(): int
    {
        return (static::IP_CLASS)::IP_VERSION;
    }

    /**
     * @param mixed $value
     *
     * @return IPBlock
     */
    public function plus($value): IPBlock
    {
        if (!(is_int($value) || (is_numeric($value) && !is_float($value)) || $value instanceof \GMP)) {
            throw new \InvalidArgumentException('Invalid value type: '.gettype($value));
        }

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
                $this->prefix_length
            );
        } catch (\InvalidArgumentException $e) {
            throw new \OutOfBoundsException($e->getMessage());
        }
    }

    /**
     * @param mixed $value
     *
     * @return IPBlock
     */
    public function minus($value): IPBlock
    {
        if (!(is_int($value) || (is_numeric($value) && !is_float($value)) || $value instanceof \GMP)) {
            throw new \InvalidArgumentException('Invalid value type: '.gettype($value));
        }

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
                $this->prefix_length
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
     * A string representation of the block with the mask in prefix notation.
     * Default method when trying to convert to a string
     *
     * @return string
     */
    public function withPrefixLength(): string
    {
        return $this->first_ip.'/'.$this->prefix_length;
    }

    /**
     * A string representation of the block with the mask in prefix notation.
     * Default method when trying to convert to a string
     *
     * @return string
     */
    public function withNetmask(): string
    {
        return $this->first_ip.'/'.$this->getNetmask();
    }

    /**
     * @deprecated
     */
    public function getGivenIpWithPrefixLen(): string
    {
        trigger_error(__METHOD__." is deprecated and will be removed. Use getGivenIpWithPrefixLength()", E_USER_DEPRECATED);
        return $this->getGivenIpWithPrefixLength();
    }

    /**
     * A string representation of the given IP, with the mask in prefix notation.
     *
     * @return string
     */
    public function getGivenIpWithPrefixLength(): string
    {
        return $this->given_ip.'/'.$this->prefix_length;
    }

    /**
     * A string representation of the given IP, with the mask in net mask notation
     *
     * @return string
     **/
    public function getGivenIpWithNetmask(): string
    {
        return $this->given_ip.'/'.$this->getNetmask();
    }

    /**
     * @internal
     * Check if the prefix_length is valid
     *
     * @param mixed $prefix_length
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    protected function checkPrefixLength($prefix_length)
    {
        if ((!is_int($prefix_length) && !ctype_digit($prefix_length)) || $prefix_length < 0 || $prefix_length > $this->getMaxPrefixLength()) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid IPv%s block prefix length '%s'",
                $this->getVersion(),
                is_scalar($prefix_length) ? $prefix_length : gettype($prefix_length)
            ));
        }

        return (int) $prefix_length;
    }

    /**
     * Split the block into smaller blocks.
     *
     * Returns an iterator, use foreach to loop it and count to get number of subnets.
     *
     * @param mixed $prefix_length
     *
     * @return IPBlockIterator
     */
    public function getSubBlocks($prefix_length): IPBlockIterator
    {
        if (is_string($prefix_length)) {
            $prefix_length = ltrim($prefix_length, '/');
        }

        $prefix_length = $this->checkPrefixLength($prefix_length);

        if ($prefix_length <= $this->prefix_length) {
            throw new \InvalidArgumentException("prefix_length must be smaller than {$this->prefix_length} ($prefix_length given)");
        }

        $first_block = new static($this->first_ip, $prefix_length);
        $number_of_blocks = gmp_pow(2, $prefix_length - $this->prefix_length);

        return new IPBlockIterator($first_block, $number_of_blocks);
    }

    /**
     * Return the super block containing the current block.
     *
     * @param mixed $prefix_length
     *
     * @return IPBlock
     */
    public function getSuperBlock($prefix_length): IPBlock
    {
        if (is_string($prefix_length)) {
            $prefix_length = ltrim($prefix_length, '/');
        }

        $prefix_length = $this->checkPrefixLength($prefix_length);

        if ($prefix_length >= $this->prefix_length) {
            throw new \InvalidArgumentException("prefix_length must be bigger than {$this->prefix_length} ($prefix_length given)");
        }

        return new static($this->first_ip, $prefix_length);
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
            $ip_class = static::IP_CLASS;
            $ip = new $ip_class($ip);
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
            $this->nb_addresses = gmp_strval(gmp_pow(2, $this->getMaxPrefixLength() - $this->prefix_length));
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
    public function offsetSet($offset, $value): void
    {
        throw new \LogicException('Setting IP in block is not supported');
    }

    /**
     * Method is logically unsupported.
     *
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        throw new \LogicException('Unsetting IP in block is not supported');
    }
}
