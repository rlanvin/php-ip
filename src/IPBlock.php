<?php

/**
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 * @link https://github.com/rlanvin/php-ip 
 */

/**
 * Base class to manipulate CIDR block (aka "networks").
 */
abstract class IPBlock implements Iterator, ArrayAccess, Countable
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
	 * @var Numeric string
	 */
	protected $nb_addresses;

	/**
	 * Return netmask
	 *
	 * @return IPv6
	 */
	public function getMask()
	{
		if ( $this->mask === null ) {
			if ( $this->prefix == 0 ) {
				$this->mask = new $this->ip_class(0);
			}
			else {
				$max_int = gmp_init(constant("$this->ip_class::MAX_INT"));
				$mask = gmp_shiftl($max_int, constant("$this->ip_class::NB_BITS") - $this->prefix);
				$mask = gmp_and($mask, $max_int); // truncate to 128 bits only
				$this->mask = new $this->ip_class($mask);
			}
		}
		return $this->mask;
	}

	/**
	 * Return delta to last IP address
	 *
	 * @return IPv6
	 */
	public function getDelta()
	{
		if ( $this->delta === null ) {
			if ( $this->prefix == 0 ) {
				$this->delta = new $this->ip_class(constant("$this->ip_class::MAX_INT"));
			}
			else {
				$this->delta = new $this->ip_class(gmp_sub(gmp_shiftl(1, constant("$this->ip_class::NB_BITS") - $this->prefix),1));
			}
		}
		return $this->delta;
	}

	/**
	 * Factory method.
	 */
	public static function create($ip, $prefix = '')
	{
		try {
			return new IPv4Block($ip, $prefix);
		} catch ( InvalidArgumentException $e ) {
			// do nothing
		}

		try {
			return new IPv6Block($ip, $prefix);
		} catch ( InvalidArgumentException $e ) {
			// do nothing
		}

		throw new InvalidArgumentException("$ip does not appear to be an IPv4 or IPv6 block");
	}

	/**
	 * Accepts a CIDR string (e.g. 192.168.0.0/24) or an IP and a prefix as
	 * two separate parameters
	 *
	 * @param $ip     mixed  IP or CIDR string
	 * @param $prefix int    (optional) The "slash" part
	 */
	public function __construct($ip_or_cidr, $prefix = '')
	{
		$this->given_ip = $ip_or_cidr;
		if ( strpos($ip_or_cidr, '/') !== false ) {
			list($this->given_ip, $prefix) = explode('/', $ip_or_cidr, 2);
		}

		if ( ! $this->given_ip instanceof IP ) {
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
	 * Returns the prefix (the slash part)
	 *
	 * @return int
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	public function getMaxPrefix()
	{
		return constant("$this->ip_class::NB_BITS");
	}

	public function getVersion()
	{
		return constant("$this->ip_class::IP_VERSION");
	}

	public function plus($value)
	{
		if ( $value < 0 ) {
			return $this->minus(-1*$value);
		}

		if ( ! is_int($value) ) {
			throw new InvalidArgumentException('plus() takes an integer');
		}

		if ( $value == 0 ) {
			return clone $this;
		}

		// check boundaries
		try { 
			$first_ip = $this->first_ip->plus(gmp_mul($value, $this->getNbAddresses()));
			return new $this->class(
				$first_ip,
				$this->prefix
			);
		} catch ( InvalidArgumentException $e ) {
			throw new OutOfBoundsException($e->getMessage());
		}
	}

	public function minus($value)
	{
		if ( $value < 0 ) {
			return $this->plus(-1*$value);
		}

		if ( ! is_int($value) ) {
			throw new InvalidArgumentException('plus() takes an integer');
		}

		if ( $value == 0 ) {
			return clone $this;
		}

		// check boundaries
		try { 
			$first_ip = $this->first_ip->minus(gmp_mul($value, $this->getNbAddresses()));
			return new $this->class(
				$first_ip,
				$this->prefix
			);
		} catch ( InvalidArgumentException $e ) {
			throw new OutOfBoundsException($e->getMessage());
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
	 * @return IP
	 */
	public function getBroadcastAddress()
	{
		return $this->last_ip;
	}

	/**
	 * A string representation of the given IP with the mask in prefix notation
	 *
	 * @return string
	 */
	public function getGivenIpWithPrefixlen()
	{
		return $this->given_ip . "/" . $this->prefix;
	}

	/**
	 * A string representation of the given IP with the network as a net mask.
	 *
	 * @return string
	 **/
	public function getGivenIpWithNetmask()
	{
		return $this->given_ip . "/" . $this->getMask();
	}

	/**
	 * @internal
	 * Check if the prefix is valid
	 *
	 * @throws InvalidArgumentException
	 */
	protected function checkPrefix($prefix)
	{
		if ( $prefix === '' || $prefix === null || $prefix === false || $prefix < 0 || $prefix > $this->getMaxPrefix() ) {
			throw new InvalidArgumentException(sprintf(
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
	 * @return IPBlockIterator
	 */
	public function getSubblocks($prefix)
	{
		$prefix = ltrim($prefix,'/');
		$this->checkPrefix($prefix);

		if ( $prefix <= $this->prefix ) {
			throw new InvalidArgumentException("Prefix must be smaller than {$this->prefix} ($prefix given)");
		}

		$first_block = new $this->class($this->first_ip, $prefix);
		$number_of_blocks = gmp_pow(2, $prefix - $this->prefix);

		return new IPBlockIterator($first_block, $number_of_blocks);
	}

	/**
	 * Return the superblock containing the current block.
	 *
	 * @return IPBlock
	 */
	public function getSuper($prefix)
	{
		$prefix = ltrim($prefix,'/');
		$this->checkPrefix($prefix);

		if ( $prefix >= $this->prefix ) {
			throw new InvalidArgumentException("Prefix must be bigger than {$this->prefix} ($prefix given)");
		}

		return new $this->class($this->first_ip, $prefix);
	}

	/**
	 * Determine if the current block contains an IP address or block.
	 *
	 * @param $ip_or_block mixed
	 * @return bool
	 */
	public function contains($ip_or_block)
	{
		if ( (is_string($ip_or_block) && strpos($ip_or_block,'/') !== false) || $ip_or_block instanceof IPBlock ) {
			return $this->containsBlock($ip_or_block);
		}
		else {
			return $this->containsIP($ip_or_block);
		}
	}

	/**
	 * Determine if the current block contains an IP address
	 *
	 * @param  $ip mixed
	 * @return bool
	 */
	public function containsIP($ip)
	{
		if ( ! $ip instanceof IP ) {
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
	 * @param  $ip mixed
	 * @return bool
	 */
	public function containsBlock($block)
	{
		if ( ! $block instanceof IPBlock ) {
			$block = new $this->class($block);
		}

		return $block->getFirstIp()->numeric() >= $this->first_ip->numeric() && $block->getLastIp()->numeric() <= $this->last_ip->numeric();
	}

	/**
	 * Determine if the current block is contained in another block.
	 *
	 * @param $block mixed
	 * @return bool
	 */
	public function isIn($block)
	{
		if ( ! $block instanceof IPBlock ) {
			$block = new $this->class($block);
		}

		return $block->containsBlock($this);
	}

	/**
	 * Test is the two blocks overlap, i.e. if block1 contains block2, or block2 contains block1
	 *
	 * @param $block mixed
	 * @return bool
	 */
	public function overlaps($block)
	{
		if ( ! $block instanceof IPBlock ) {
			$block = new $this->class($block);
		}

		return ! ($block->getFirstIp()->numeric() > $this->last_ip->numeric() || $block->getLastIp()->numeric() < $this->first_ip->numeric());
	}

	/**
	 * Return the number of IP addresses in the block.
	 *
	 * @return string numeric string (can be huge)
	 */
	public function getNbAddresses()
	{
		if ( $this->nb_addresses === null ) {
			$this->nb_addresses = gmp_strval(gmp_pow(2, $this->getMaxPrefix() - $this->prefix));
		}
		return $this->nb_addresses;
	}

// Countable
	public function count()
	{
		$n = $this->getNbAddresses();
		if ( $n > PHP_INT_MAX ) {
			throw new RuntimeException('The number of addresses is bigger than PHP_INT_MAX, use getNbAddresses() instead');
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
		$this->position = gmp_add($this->position,1);
	}

	public function valid()
	{
		return gmp_cmp($this->position,0) >= 0 && gmp_cmp($this->position, $this->getNbAddresses()) < 0;
	}

// ArrayAccess

	public function offsetExists($offset)
	{
		return gmp_cmp($offset,0) >= 0 && gmp_cmp($offset, $this->getNbAddresses()) < 0;
	}

	public function offsetGet($offset)
	{
		if ( ! $this->offsetExists($offset) ) {
			throw new OutOfBoundsException("Offset $offset does not exists");
		}
		return $this->first_ip->plus($offset);
	}

	public function offsetSet($offset, $value)
	{
		throw new LogicException('Setting IP in block is not supported');
	}

	public function offsetUnset($offset)
	{
		throw new LogicException('Unsetting IP in block is not supported');
	}
}