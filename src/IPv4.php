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
		if ( is_int($ip) || (is_float($ip) && floor($ip) == $ip) ) {
			$this->ip = gmp_init(sprintf('%u',$ip),10);
		}
		elseif ( is_string($ip) ) {
			if ( ! ctype_print($ip) ) {
				if ( strlen($ip) != 4 ) {
					throw new InvalidArgumentException("The binary string is not a valid IPv4 address");
				}
				$hex = unpack('H*',$ip);
				$this->ip = gmp_init($hex[1],16);
			}
			elseif ( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
				$this->ip = gmp_init(sprintf('%u',ip2long($ip)));
			}
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
		elseif ( is_resource($ip) &&  get_resource_type($ip) == 'GMP integer') {
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