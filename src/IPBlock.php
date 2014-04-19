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

	abstract public function getDelta();
	abstract public function getMask();

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

	public function __construct(IP $ip, $prefix)
	{
		$this->checkPrefix($prefix);

		$this->prefix = (int) $prefix;

		$delta = $this->getDelta();
		$mask = $this->getMask();

		$this->first_ip = $ip->bit_and($mask);
		$this->last_ip = $this->first_ip->bit_or($delta);
	}

	public function __toString()
	{
		return (string) $this->first_ip.'/'.$this->prefix;
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

	abstract public function getMaxPrefix();

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
	public function split($prefix)
	{
		$prefix = ltrim($prefix,'/');
		$this->checkPrefix($prefix);

		if ( $prefix <= $this->prefix ) {
			throw new InvalidArgumentException("$prefix is not smaller than {$this->prefix}");
		}
		if ( $prefix - $this->prefix >= 32 ) {
			throw new InvalidArgumentException("You cannot split directly into more than 32bits depth, that would create memory problems");
		}

		$first_block = new static($this->first_ip, $prefix);
		$number_of_blocks = pow(2, $prefix - $this->prefix);

		return new IPBlockIterator($first_block, $number_of_blocks);
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
			// $block = new static($block);
			$block = IPBlock::create($block);
		}

		return $block->getFirstIp()->numeric() >= $this->first_ip->numeric() && $block->getLastIp()->numeric() <= $this->last_ip->numeric();
	}

	/**
	 * Determine if the current block is contained in another block.
	 *
	 * @param $block mixed
	 * @return bool
	 */
	public function isContainedIn($block)
	{
		if ( ! $block instanceof IPBlock ) {
			// $block = new static($block);
			$block = IPBlock::create($block);
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
			// $block = new static($block);
			$block = IPBlock::create($block);
		}

		return ! ($block->getFirstIp()->numeric() > $this->last_ip->numeric() || $block->getLastIp()->numeric() < $this->first_ip->numeric());
	}

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
			throw new RuntimeException('The number of addresses is bigger than PHP_INT_MAX. Use getNbAddresses() instead. Sorry!');
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