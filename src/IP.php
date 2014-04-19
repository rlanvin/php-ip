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
 * Base class to manipulate an IP address.
 */
abstract class IP
{
	/**
	 * Internal representation of the IP as a numeric format.
	 * For IPv4, this will be an SIGNED int (32 bits).
	 * For IPv6, this will be a GMP ressource (128 bits big int).
	 * @var mixed
	 */
	protected $ip;

	/**
	 * @var bool
	 */
	protected $is_private;

	/**
	 * Take an IP string/int and return an object of the correct type.
	 * 
	 * Either IPv4 or IPv6 may be supplied, but integers less than 2^32 will
	 * be considered to be IPv4 by default.
	 *
	 * @param  $ip      mixed Anything that can be converted into an IP (string, int, bin, etc.)
	 * @return IPv4 or IPv6
	 */
	public static function create($ip)
	{
		try {
			return new IPv4($ip);
		} catch ( InvalidArgumentException $e ) {
			// do nothing
		}

		try {
			return new IPv6($ip);
		} catch ( InvalidArgumentException $e ) {
			// do nothing
		}

		throw new InvalidArgumentException("$ip does not appear to be an IPv4 or IPv6 address");
	}

	/**
	 * Return human readable representation of the IP (e.g. 127.0.0.1 or ::1)
	 *
	 * @return string
	 */
	abstract public function humanReadable();

	/**
	 * Return numeric representation of the IP in base $base.
	 *
	 * The return value is a PHP string. It can base used for comparaison.
	 *
	 * @param  $base  int from 2 to 36
	 * @return string
	 */
	abstract public function numeric($base = 10);

	/**
	 * Bitwise AND
	 */
	abstract public function bit_and($value);

	/**
	 * Bitwise OR
	 */
	abstract public function bit_or($value);

	/**
	 * Addition
	 */
	abstract public function plus($value);

	/**
	 * Subtraction
	 */
	abstract public function minus($value);

	/**
	 * @see humanReadable()
	 */
	public function __toString()
	{
		return $this->humanReadable();
	}

	/**
	 * Return the version number (4 or 6).
	 *
	 * Note: this is left abstract because there is not late static binding
	 * in PHP 5.2 (which I need to support).
	 *
	 * @return int
	 */
	abstract public function getVersion();

	/**
	 * Check if the IP is contained in given block.
	 *
	 * @param $block mixed Anything that can be converted into an IPBlock
	 * @return bool
	 */
	public function isIn($block)
	{
		if ( ! $block instanceof IPBlock ) {
			$block = IPBlock::create($block);
		}

		return $block->contains($this);
	}

	abstract public function isPrivate();

	/**
	 * Return true if the address is allocated for public networks
	 *
	 * @return bool
	 */
	public function isPublic()
	{
		return ! $this->isPrivate();
	}
}