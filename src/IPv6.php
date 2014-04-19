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
 * Class to manipulate IPv6
 *
 * Addresses are stored internally as GMP ressource (big int).
 */
class IPv6 extends IP
{
	const IP_VERSION = 6;
	const MAX_INT = '340282366920938463463374607431768211455';
	const NB_BITS = 128;

	public function getVersion()
	{
		return self::IP_VERSION;
	}

	/**
	 * Constuctor tries to guess what is $ip.
	 *
	 * @param mixed $ip
	 */
	public function __construct($ip)
	{
		if ( is_int($ip) ) {
			$this->ip = gmp_init(sprintf('%u',$ip),10);
		}
		elseif ( is_string($ip) ) {
			// binary string
			if ( ! ctype_print($ip) ) {
				// probably the result of inet_pton
				// must be 16 bytes exactly to be valid
				if ( strlen($ip) != 16 ) {
					throw new InvalidArgumentException("The binary string is not a valid IPv6 address");
				}
				$hex = unpack('H*',$ip);
				$this->ip = gmp_init($hex[1],16);
			}
			// valid human readable representation
			elseif ( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ) {
				$ip = inet_pton($ip);
				$hex = unpack('H*',$ip);
				$this->ip = gmp_init($hex[1],16);
			}
			// decimal value ?
			elseif ( ctype_digit($ip) ) {
				if ( gmp_cmp($ip, '340282366920938463463374607431768211455') > 0 ) {
					throw new InvalidArgumentException("$ip is not a valid decimal IPv6 address");
				}
				$this->ip = gmp_init($ip,10);
			}
			else {
				throw new InvalidArgumentException("$ip is not a valid IPv6 address");
			}
		}
		elseif ( is_resource($ip) &&  get_resource_type($ip) == 'GMP integer') {
			if ( gmp_cmp($ip, '-1') <= 0 || gmp_cmp($ip, '340282366920938463463374607431768211455') > 0 ) {
				throw new InvalidArgumentException(sprintf("%s is not a valid decimal IPv6 address", gmp_strval($ip)));
			}
			$this->ip = $ip;
		}
		else {
			throw new InvalidArgumentException("Unsupported argument type: ".gettype($ip));
		}
	}

	/**
	 * Returns a numeric representation of the IP as a PHP string.
	 *
	 * Note: The result will not be padded, i.e. leading 0 are removed
	 *
	 * @param  $base int
	 * @return string
	 */
	public function numeric($base = 10)
	{
		if ( $base < 2 || $base > 36 ) {
			throw new InvalidArgumentException("Base must be between 2 and 36 (included)");
		}

		return gmp_strval($this->ip, $base);
	}

	/**
	 * Returns human readable representation of the IP
	 *
	 * @param $compress bool Wether to compress IPv6 or not
	 * @return string
	 */
	public function humanReadable($compress = true)
	{
		$hex = $this->numeric(16);
		$hex =str_pad($hex, 32, '0', STR_PAD_LEFT);
		$bytes = str_split($hex,4);
		$ip = implode(':',$bytes);

		if ( $compress ) {
			$ip = @ inet_ntop(@ inet_pton($ip));
		}

		return $ip;
	}

	/**
	 * Bitwise AND
	 *
	 * @param $value mixed anything that can be converted into an IP object
	 * @return IP
	 */
	public function bit_and($value)
	{
		if ( ! $value instanceof self ) {
			$value = new self($value);
		}

		return new self(gmp_and($this->ip, $value->ip));
	}

	/**
	 * Bitwise OR
	 *
	 * @param $value mixed anything that can be converted into an IP object
	 * @return IP
	 */
	public function bit_or($value)
	{
		if ( ! $value instanceof self ) {
			$value = new self($value);
		}

		return new self(gmp_or($this->ip, $value->ip));
	}

	/**
	 * Plus (+)
	 *
	 * @throws OutOfBoundsException
	 * @param $value mixed anything that can be converted into an IP object
	 * @return IP
	 */
	public function plus($value)
	{
		if ( $value < 0 ) {
			return $this->minus(-1*$value);
		}

		if ( $value == 0 ) {
			return clone $this;
		}

		if ( ! $value instanceof self ) {
			$value = new self($value);
		}

		$result = gmp_add($this->ip, $value->ip);

		if ( gmp_cmp($result,0) < 0 || gmp_cmp($result,self::MAX_INT) > 0 ) {
			throw new OutOfBoundsException();
		}

		return new self($result);
	}

	/**
	 * Minus(-)
	 *
	 * @throws OutOfBoundsException
	 * @param $value mixed anything that can be converted into an IP object
	 * @return IP
	 */
	public function minus($value)
	{
		if ( $value < 0 ) {
			return $this->plus(-1*$value);
		}

		if ( $value == 0 ) {
			return clone $this;
		}

		if ( ! $value instanceof self ) {
			$value = new self($value);
		}

		$result = gmp_sub($this->ip, $value->ip);

		if ( gmp_cmp($result,0) < 0 || gmp_cmp($result,self::MAX_INT) > 0 ) {
			throw new OutOfBoundsException();
		}

		return new self($result);
	}

	/**
	 * Return true if the address is reserved per iana-ipv6-special-registry
	 */
	public function isPrivate()
	{
		if ( $this->is_private === null ) {
			$this->is_private =
				$this->isIn('::1/128') ||
				$this->isIn('::/128') ||
				$this->isIn('::ffff:0:0/96') ||
				$this->isIn('100::/64') ||
				$this->isIn('2001::/23') ||
				$this->isIn('2001:2::/48') ||
				$this->isIn('2001:db8::/32') ||
				$this->isIn('2001:10::/28') ||
				$this->isIn('fc00::/7') ||
				$this->isIn('fe80::/10');
		}
		return $this->is_private;
	}
}