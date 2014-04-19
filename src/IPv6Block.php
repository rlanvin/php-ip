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
 * An IPv6 CIDR block
 */
class IPv6Block extends IPBlock
{
	public function getVersion()
	{
		return IPv6::IP_VERSION;
	}

	public function getMaxPrefix()
	{
		return IPv6::NB_BITS;
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
			$ip = new IPv6($ip);
		}

		parent::__construct($ip, $prefix);
	}

	/**
	 * Return netmask
	 *
	 * @return IPv6
	 */
	public function getMask()
	{
		if ( $this->prefix == 0 ) {
			return new IPv6(0);
		}
		$max_int = gmp_init(IPv6::MAX_INT);
		$mask = gmp_shiftl($max_int, IPv6::NB_BITS - $this->prefix);
		$mask = gmp_and($mask, $max_int); // truncate to 128 bits only
		return new IPv6($mask);
	}

	/**
	 * Return delta to last IP address
	 *
	 * @return IPv6
	 */
	public function getDelta()
	{
		if ( $this->prefix == 0 ) {
			return new IPv6(IPv6::MAX_INT);
		}
		return new IPv6(gmp_sub(gmp_shiftl(1, IPv6::NB_BITS - $this->prefix),1));
	}
}