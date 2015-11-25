<?php

/**
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 * @link https://github.com/rlanvin/php-ip 
 */

if ( ! function_exists('gmp_shiftl') ) {
	/**
	 * Shift left (<<)
	 * @link http://www.php.net/manual/en/ref.gmp.php#99788
	 */
	function gmp_shiftl($x, $n)
	{
		return gmp_mul($x, gmp_pow('2', $n));
	}
}
if ( ! function_exists('gmp_shiftr') ) {
	/**
	 * Shift right (>>)
	 * @link http://www.php.net/manual/en/ref.gmp.php#99788
	 */
	function gmp_shiftr($x, $n)
	{
		return gmp_div($x, gmp_pow('2', $n));
	}
}

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
	public function numeric($base = 10)
	{
		if ( $base < 2 || $base > 36 ) {
			throw new InvalidArgumentException("Base must be between 2 and 36 (included)");
		}

		$value = gmp_strval($this->ip, $base);

		// fix for newer versions of GMP (> 5.0) in PHP 5.4+ that removes
		// the leading 0 in base 2
		if ( $base == 2 ) {
			$n = constant("$this->class::NB_BITS"); // ugly, but necessary because of PHP 5.2
			$value = str_pad($value, $n, '0', STR_PAD_LEFT);
		}

		return $value;
	}

	/**
	 * Return binary string representation
	 *
	 * @todo could be optimized with pack() instead?
	 *
	 * @return string Binary string
	 */
	public function binary()
	{
		return inet_pton($this->humanReadable());
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
			$value = new $this->class($value);
		}

		return new $this->class(gmp_and($this->ip, $value->ip));
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
			$value = new $this->class($value);
		}

		return new $this->class(gmp_or($this->ip, $value->ip));
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
			$value = new $this->class($value);
		}

		$result = gmp_add($this->ip, $value->ip);

		if ( gmp_cmp($result,0) < 0 || gmp_cmp($result, constant("$this->class::MAX_INT")) > 0 ) {
			throw new OutOfBoundsException();
		}

		return new $this->class($result);
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
			$value = new $this->class($value);
		}

		$result = gmp_sub($this->ip, $value->ip);

		if ( gmp_cmp($result,0) < 0 || gmp_cmp($result, constant("$this->class::MAX_INT")) > 0 ) {
			throw new OutOfBoundsException();
		}

		return new $this->class($result);
	}


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
/**
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 * @link https://github.com/rlanvin/php-ip 
 */

/**
 * Class to manipulate IPv4
 */
class IPv4 extends IP
{
	const IP_VERSION = 4;
	const MAX_INT = '4294967295';
	const NB_BITS = 32;

	/**
	 * Workaround for lack of late static binding in PHP 5.2
	 * so I can use "new $this->class()"" instead of "new static()"
	 */
	protected $class = __CLASS__;

	public function getVersion()
	{
		return self::IP_VERSION;
	}

	/**
	 * Constructor tries to guess what is the $ip
	 *
	 * @param $ip mixed String, binary string, int or float
	 */
	public function __construct($ip)
	{
		if ( is_int($ip) ) {
			// if an integer is provided, we have to be careful of the architecture
			// on 32 bits plateform, it's always a valid IP
			// on 64 bits plateform, we have to test the value
			$ip = gmp_init(sprintf('%u',$ip),10);
			if ( gmp_cmp($ip, self::MAX_INT) > 0 ) {
				throw new InvalidArgumentException(sprintf('The integer %s is not a valid IPv4 address', gmp_strval($ip)));
			}
			$this->ip = $ip;
		}
		elseif ( is_float($ip) && floor($ip) == $ip ) {
			// float (or double) with an integer value
			$ip = gmp_init(sprintf('%s',$ip), 10);
			if ( gmp_cmp($ip, 0) < 0 || gmp_cmp($ip, self::MAX_INT) > 0 ) {
				throw new InvalidArgumentException(sprintf('The double %s is not a valid IPv4 address', gmp_strval($ip)));
			}
			$this->ip = $ip;
		}
		elseif ( is_string($ip) ) {
			// binary string
			if ( ! ctype_print($ip) ) {
				if ( strlen($ip) != 4 ) {
					throw new InvalidArgumentException("The binary string is not a valid IPv4 address");
				}
				$hex = unpack('H*',$ip);
				$this->ip = gmp_init($hex[1],16);
			}
			// human readable IPv4
			elseif ( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
				$this->ip = gmp_init(sprintf('%u',ip2long($ip)));
			}
			// numeric string (decimal)
			elseif ( ctype_digit($ip) ) {
				$ip = gmp_init($ip);
				if ( gmp_cmp($ip, self::MAX_INT) > 0 ) {
					throw new InvalidArgumentException(sprintf("%s is not a valid decimal IPv4 address", gmp_strval($ip)));
				}
				
				$this->ip = $ip;
			}
			else {
				throw new InvalidArgumentException("$ip is not a valid IPv4 address");
			}
		}
		elseif ( (is_resource($ip) && get_resource_type($ip) == 'GMP integer') || $ip instanceof GMP ) {
			if ( gmp_cmp($ip, 0) < 0 || gmp_cmp($ip, self::MAX_INT) > 0 ) {
				throw new InvalidArgumentException(sprintf("%s is not a valid decimal IPv4 address", gmp_strval($ip)));
			}
			$this->ip = $ip;
		}
		else {
			throw new InvalidArgumentException("Unsupported argument type: ".gettype($ip));
		}
	}

	/**
	 * Returns human readable representation of the IP
	 *
	 * @param $compress bool Wether to compress IPv4 or not
	 * @return string
	 */
	public function humanReadable($compress = true)
	{
		if ( $compress ) {
			$ip = long2ip(intval(doubleval($this->numeric())));
		}
		else {
			$hex = $this->numeric(16);
			$hex = str_pad($hex, 8, '0', STR_PAD_LEFT);
			$segments = str_split($hex, 2);
			foreach ( $segments as & $s ) {
				$s = str_pad(base_convert($s,16,10), 3, '0', STR_PAD_LEFT);
			}
			$ip = implode('.',$segments);
		}

		return $ip;
	}

	/**
	 * Return true if the address is reserved per iana-ipv4-special-registry
	 */
	public function isPrivate()
	{
		if ( $this->is_private === null ) {
			$this->is_private =
				$this->isIn('0.0.0.0/8') ||
				$this->isIn('10.0.0.0/8') ||
				$this->isIn('127.0.0.0/8') ||
				$this->isIn('169.254.0.0/16') ||
				$this->isIn('172.16.0.0/12') ||
				$this->isIn('192.0.0.0/29') ||
				$this->isIn('192.0.0.170/31') ||
				$this->isIn('192.0.2.0/24') ||
				$this->isIn('192.168.0.0/16') ||
				$this->isIn('198.18.0.0/15') ||
				$this->isIn('198.51.100.0/24') ||
				$this->isIn('203.0.113.0/24') ||
				$this->isIn('240.0.0.0/4') ||
				$this->isIn('255.255.255.255/32');
		}
		return $this->is_private;
	}
}
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

	/**
	 * Workaround for lack of late static binding in PHP 5.2
	 * so I can use "new $this->class()"" instead of "new static()"
	 */
	protected $class = __CLASS__;

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
			// always a valid IP, since even in 64bits plateform, it's less than max value
			$this->ip = gmp_init(sprintf('%u',$ip),10);
		}
		elseif ( is_float($ip) && floor($ip) == $ip ) {
			// float (or double) with an integer value
			$ip = gmp_init(sprintf('%s',$ip), 10);
			if ( gmp_cmp($ip, 0) < 0 || gmp_cmp($ip, self::MAX_INT) > 0 ) {
				throw new InvalidArgumentException(sprintf('The double %s is not a valid IPv6 address', gmp_strval($ip)));
			}
			$this->ip = $ip;
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
			// numeric string (decimal)
			elseif ( ctype_digit($ip) ) {
				$ip = gmp_init($ip, 10);
				if ( gmp_cmp($ip, '340282366920938463463374607431768211455') > 0 ) {
					throw new InvalidArgumentException(sprintf("%s is not a valid decimal IPv6 address", gmp_strval($ip)));
				}
				$this->ip = $ip;
			}
			else {
				throw new InvalidArgumentException("$ip is not a valid IPv6 address");
			}
		}
		elseif ( (is_resource($ip) && get_resource_type($ip) == 'GMP integer') || $ip instanceof GMP ) {
			if ( gmp_cmp($ip, 0) < 0 || gmp_cmp($ip, self::MAX_INT) > 0 ) {
				throw new InvalidArgumentException(sprintf("%s is not a valid decimal IPv6 address", gmp_strval($ip)));
			}
			$this->ip = $ip;
		}
		else {
			throw new InvalidArgumentException("Unsupported argument type: ".gettype($ip));
		}
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
		$hex = str_pad($hex, 32, '0', STR_PAD_LEFT);
		$bytes = str_split($hex,4);
		$ip = implode(':',$bytes);

		if ( $compress ) {
			$ip = @ inet_ntop(@ inet_pton($ip));
		}

		return $ip;
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
		$ip = $ip_or_cidr;
		if ( strpos($ip_or_cidr, '/') !== false ) {
			list($ip, $prefix) = explode('/', $ip_or_cidr, 2);
		}

		if ( ! $ip instanceof IP ) {
			$ip = new $this->ip_class($ip);
		}

		$this->checkPrefix($prefix);
		$this->prefix = (int) $prefix;

		$this->first_ip = $ip->bit_and($this->getMask());
		$this->last_ip = $this->first_ip->bit_or($this->getDelta());
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
/**
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 * @link https://github.com/rlanvin/php-ip 
 */

/**
 * An IPv4 CIDR block
 */
class IPv4Block extends IPBlock
{
	protected $ip_class = 'IPv4';
	protected $class = __CLASS__;
}
/**
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 * @link https://github.com/rlanvin/php-ip 
 */

/**
 * An IPv6 CIDR block
 */
class IPv6Block extends IPBlock
{
	protected $ip_class = 'IPv6';
	protected $class = __CLASS__;
}
/**
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 * @link https://github.com/rlanvin/php-ip 
 */

/**
 * Iterator for IPBlock. This could be a Generator in PHP 5.5
 */
class IPBlockIterator implements Iterator
{
	protected $position = 0;
	protected $current_block = null;

	protected $first_block = null;
	protected $nb_blocks = 0;

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
		$this->position = gmp_add($this->position,1);
		$this->current_block = $this->current_block->plus(1);
	}

	public function valid()
	{
		return gmp_cmp($this->position,0) >= 0 && gmp_cmp($this->position, $this->nb_blocks) < 0;
	}

}