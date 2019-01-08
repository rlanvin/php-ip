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
abstract class IPBlock implements \Iterator, \ArrayAccess, \Countable
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
    protected $ip_class;

    /**
     * Return netmask.
     *
     * @return IP
     */
    public function getMask(): IP
    {
        if (null === $this->mask) {
            if (0 == $this->prefix) {
                $this->mask = new $this->ip_class(0);
            } else {
                $max_int = gmp_init(($this->ip_class)::MAX_INT);
                $mask = self::gmp_shiftl($max_int, ($this->ip_class)::NB_BITS - $this->prefix);
                $mask = gmp_and($mask, $max_int); // truncate to 128 bits only
                $this->mask = new $this->ip_class($mask);
            }
        }

        return $this->mask;
    }

    /**
     * Return delta to last IP address.
     *
     * @return IP
     */
    public function getDelta()
    {
        if (null === $this->delta) {
            if (0 == $this->prefix) {
                $this->delta = new $this->ip_class(($this->ip_class)::MAX_INT);
            } else {
                $this->delta = new $this->ip_class(gmp_sub(self::gmp_shiftl(1, ($this->ip_class)::NB_BITS - $this->prefix), 1));
            }
        }

        return $this->delta;
    }

    /**
     * Factory method.
     *
     * @param $ip
     * @param string $prefix
     *
     * @return IPv4Block|IPv6Block
     */
    public static function create($ip, $prefix = '')
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
     * @param mixed $prefix     (optional) The "slash" part
     */
    public function __construct($ip_or_cidr, $prefix = '')
    {
        $this->given_ip = $ip_or_cidr;
        if (false !== strpos($ip_or_cidr, '/')) {
            list($this->given_ip, $prefix) = explode('/', $ip_or_cidr, 2);
        }

        if (!$this->given_ip instanceof IP) {
            $this->given_ip = new $this->ip_class($this->given_ip);
        }

        $this->checkPrefix($prefix);
        $this->prefix = (int) $prefix;

        $this->first_ip = $this->given_ip->bit_and($this->getMask());
        $this->last_ip = $this->first_ip->bit_or($this->getDelta());
    }

    public function __toString()
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
    public function getGivenIp()
    {
        return $this->given_ip;
    }

    /**
     * Returns the prefix (the slash part).
     *
     * @return int
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getMaxPrefix()
    {
        return ($this->ip_class)::NB_BITS;
    }

    public function getVersion()
    {
        return ($this->ip_class)::IP_VERSION;
    }

    public function plus($value)
    {
        if ($value < 0) {
            return $this->minus(-1 * $value);
        }

        if (!is_int($value)) {
            throw new \InvalidArgumentException('plus() takes an integer');
        }

        if (0 == $value) {
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

    public function minus($value)
    {
        if ($value < 0) {
            return $this->plus(-1 * $value);
        }

        if (!is_int($value)) {
            throw new \InvalidArgumentException('plus() takes an integer');
        }

        if (0 == $value) {
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
    public function getFirstIp()
    {
        return $this->first_ip;
    }

    /**
     * Returns the last IP address of the block.
     *
     * @return IP
     */
    public function getLastIp()
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
    public function getNetworkAddress()
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
    public function getBroadcastAddress()
    {
        return $this->last_ip;
    }

    /**
     * A string representation of the given IP with the mask in prefix notation.
     *
     * @return string
     */
    public function getGivenIpWithPrefixlen()
    {
        return $this->given_ip.'/'.$this->prefix;
    }

    /**
     * A string representation of the given IP with the network as a net mask.
     *
     * @return string
     **/
    public function getGivenIpWithNetmask()
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
     */
    protected function checkPrefix($prefix)
    {
        if ('' === $prefix || null === $prefix || false === $prefix || $prefix < 0 || $prefix > $this->getMaxPrefix()) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid IPv%s block prefix '%s'",
                $this->getVersion(),
                $prefix
            ));
        }
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
    public function getSubblocks($prefix)
    {
        $prefix = ltrim($prefix, '/');
        $this->checkPrefix($prefix);

        if ($prefix <= $this->prefix) {
            throw new \InvalidArgumentException("Prefix must be smaller than {$this->prefix} ($prefix given)");
        }

        $first_block = new static($this->first_ip, $prefix);
        $number_of_blocks = gmp_pow(2, $prefix - $this->prefix);

        return new IPBlockIterator($first_block, $number_of_blocks);
    }

    /**
     * Return the superblock containing the current block.
     *
     * @param mixed $prefix
     *
     * @return IPBlock
     */
    public function getSuper($prefix)
    {
        $prefix = ltrim($prefix, '/');
        $this->checkPrefix($prefix);

        if ($prefix >= $this->prefix) {
            throw new \InvalidArgumentException("Prefix must be bigger than {$this->prefix} ($prefix given)");
        }

        return new static($this->first_ip, $prefix);
    }

    /**
     * Determine if the current block contains an IP address or block.
     *
     * @param IP|IPBlock $ip_or_block mixed
     *
     * @return bool
     */
    public function contains($ip_or_block): bool
    {
        if ($this->isIpBlock($ip_or_block)) {
            return $this->containsBlock($ip_or_block);
        }

        return $this->containsIP($ip_or_block);
    }

    /**
     * @param mixed $block
     *
     * @return bool
     */
    private function isIpBlock($block): bool
    {
        if ((is_string($block) && false !== strpos($block, '/')) || $block instanceof IPBlock) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the current block contains an IP address.
     *
     * @param mixed $ip
     *
     * @return bool
     */
    public function containsIP($ip)
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
    public function containsBlock($block)
    {
        if (!$block instanceof IPBlock) {
            $block = new static($block);
        }

        return $block->getFirstIp()->numeric() >= $this->first_ip->numeric() && $block->getLastIp()->numeric() <= $this->last_ip->numeric();
    }

    /**
     * Determine if the current block is contained in another block.
     *
     * @param $block mixed
     *
     * @return bool
     */
    public function isIn($block)
    {
        if (!$block instanceof IPBlock) {
            $block = new static($block);
        }

        return $block->containsBlock($this);
    }

    /**
     * Test is the two blocks overlap, i.e. if block1 contains block2, or block2 contains block1.
     *
     * @param $block mixed
     *
     * @return bool
     */
    public function overlaps($block)
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
    public function getNbAddresses()
    {
        if (null === $this->nb_addresses) {
            $this->nb_addresses = gmp_strval(gmp_pow(2, $this->getMaxPrefix() - $this->prefix));
        }

        return $this->nb_addresses;
    }

    // Countable
    public function count()
    {
        $n = $this->getNbAddresses();
        if ($n > PHP_INT_MAX) {
            throw new \RuntimeException('The number of addresses is bigger than PHP_INT_MAX, use getNbAddresses() instead');
        }

        return $n;
    }

    // Iterator

    protected $position = 0;

    public function rewind()
    {
        $this->position = gmp_init(0);
    }

    public function current()
    {
        return $this->first_ip->plus(gmp_strval($this->position));
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position = gmp_add($this->position, 1);
    }

    public function valid()
    {
        return gmp_cmp($this->position, 0) >= 0 && gmp_cmp($this->position, $this->getNbAddresses()) < 0;
    }

    // ArrayAccess

    public function offsetExists($offset)
    {
        return gmp_cmp($offset, 0) >= 0 && gmp_cmp($offset, $this->getNbAddresses()) < 0;
    }

    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException("Offset $offset does not exists");
        }

        return $this->first_ip->plus($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Setting IP in block is not supported');
    }

    public function offsetUnset($offset)
    {
        throw new \LogicException('Unsetting IP in block is not supported');
    }

    /**
     * Shift left (<<).
     *
     * @see http://www.php.net/manual/en/ref.gmp.php#99788
     *
     * @param resource|string $x
     * @param resource|string $n
     *
     * @return resource
     */
    private static function gmp_shiftl($x, $n)
    {
        return gmp_mul($x, gmp_pow('2', $n));
    }
}
