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
 * An IPv4 CIDR block
 */
class IPv4Block extends IPBlock
{
	public function getVersion()
	{
		return IPv4::IP_VERSION;
	}

	public function getMaxPrefix()
	{
		return IPv4::NB_BITS;
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
			$ip = new IPv4($ip);
		}

		parent::__construct($ip, $prefix);
	}

	/**
	 * Return netmask
	 *
	 * @return IPv4
	 */
	public function getMask()
	{
		if ( $this->mask === null ) {
			if ( $this->prefix == 0 ) {
				$this->mask = new IPv4(0);
			}
			else {
				$this->mask = new IPv4(IPv4::MAX_INT << (IPv4::NB_BITS - $this->prefix));
			}
		}
		return $this->mask;
	}

	/**
	 * Return delta to last IP address
	 *
	 * @return IPv4
	 */
	public function getDelta()
	{
		if ( $this->delta === null ) {
			if ( $this->prefix == 0 ) {
				$this->delta = new IPv4(IPv4::MAX_INT);
			}
			else {
				$this->delta = new IPv4((1 << (IPv4::NB_BITS - $this->prefix)) - 1);
			}
		}
		return $this->delta;
	}
}