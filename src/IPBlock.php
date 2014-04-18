<?php

abstract class IPBlock
{
	const IP_VERSION = -1;
	const MAX_BITS = 0;

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

	public function __construct($ip, $prefix = '')
	{
		if ( strpos($ip, '/') !== false ) {
			list($ip,$prefix) = explode('/', $ip, 2);
		}

		$this->checkPrefix($prefix);

		$this->prefix = (int) $prefix;

		if ( ! $ip instanceof IP ) {
			$ip = IP::create($ip, static::IP_VERSION);
		}
		$delta = $this->getDelta();
		$mask = $this->getMask();
		
		$this->first_ip = $ip->bit_and($mask);
		$this->last_ip = $this->first_ip->bit_or($delta);
	}

	public function __toString()
	{
		return (string) $this->first_ip.'/'.$this->prefix;
	}

	public function getPrefix()
	{
		return $this->prefix;
	}

	public function getFirstIp()
	{
		return $this->first_ip;
	}

	public function getLastIp()
	{
		return $this->last_ip;
	}

	public function getNetworkAddress()
	{
		return $this->first_ip;
	}

	public function getBroadcastAddress()
	{
		return $this->last_ip;
	}

	protected function checkPrefix($prefix)
	{
		if ( $prefix === '' || $prefix === null || $prefix === false || $prefix < 0 || $prefix > static::MAX_BITS ) {
			throw new InvalidArgumentException(sprintf(
				"Invalid IPv%s block prefix '%s'",
				static::IP_VERSION,
				$prefix
			));
		}
	}

	abstract public function getDelta();
	abstract public function getMask();

	/**
	 * Split the block into smaller blocks.
	 */
	public function split($prefix)
	{
		$prefix = ltrim($prefix,'/');
		$this->checkPrefix($prefix);

		if ( $prefix <= $this->prefix ) {
			throw new InvalidArgumentException("'$prefix' is not smaller than '{$this->prefix}'.");
		}
		if ( $prefix - $this->prefix >= 32 ) {
			throw new InvalidArgumentException("You cannot split directly into more than 32bits depth, that would create memory problems.");
		}

		$first_block = new static($this->first_ip, $prefix);
		$number_of_blocks = pow(2, $prefix - $this->prefix);

		return new IPBlockIterator($first_block, $number_of_blocks);
	}

	/**
	 * Determine if the current block contains the given IP address.
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

	public function containsIP($ip)
	{
		if ( ! $ip instanceof IP ) {
			$ip = IP::create($ip, static::IP_VERSION);
		}

		return ($ip->numeric() >= $this->getFirstIp()->numeric()) && ($ip->numeric() <= $this->getLastIp()->numeric());
	}

	public function containsBlock($block)
	{
		if ( ! $block instanceof IPBlock ) {
			$block = new static($block);
		}

		// true in this situation:
		// $this: first_ip[                               ]last_ip
		// $block:         first_ip[             ]last_ip

		return $block->getFirstIp()->numeric() >= $this->first_ip->numeric() && $block->getLastIp()->numeric() <= $this->last_ip->numeric();
	}

	public function isContainedIn($block)
	{
		if ( ! $block instanceof IPBlock ) {
			$block = new static($block);
		}

		return $block->containsBlock($this);
	}

	/**
	 * Test is the two blocks overlap, i.e. if block1 contains block2, or block2 contains block1
	 */
	public function overlaps($block)
	{
		if ( ! $block instanceof IPBlock ) {
			$block = new static($block);
		}

		return ! ($block->getFirstIp()->numeric() > $this->last_ip->numeric() || $block->getLastIp()->numeric() < $this->first_ip->numeric());
	}
}